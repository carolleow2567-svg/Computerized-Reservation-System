<?php
require_once __DIR__ . '/../models/SeatModel.php';

class SeatController
{
    private SeatModel $model;

    public function __construct(PDO $pdo)
    {
        $this->model = new SeatModel($pdo);
    }

    /** Server-side validation of journey parameters. Returns [cleanData, errorsByField]. */
    public function parseJourney(array $src, bool $needSchedule = false): array
    {
        $data = ['from' => 0, 'to' => 0, 'date' => '', 'n' => 1, 'class' => '', 'schedule_id' => 0];
        $err  = [];

        $stations = array_column($this->model->stations(), 'name', 'station_id');
        foreach (['from' => 'departure', 'to' => 'arrival'] as $key => $label) {
            $id = filter_var($src[$key] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || !isset($stations[$id])) {
                $err[$key] = "Choose a valid $label station.";
            } else {
                $data[$key] = $id;
            }
        }
        if (!isset($err['from']) && !isset($err['to']) && $data['from'] === $data['to']) {
            $err['to'] = 'Departure and arrival stations must be different.';
        }

        $date = $src['date'] ?? '';
        $dt   = is_string($date) ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : false;
        if (!$dt || $dt->format('Y-m-d') !== $date) {
            $err['date'] = 'Enter a valid travel date.';
        } else {
            $today = new DateTimeImmutable('today');
            if ($dt < $today) {
                $err['date'] = 'Travel date cannot be in the past.';
            } elseif ($dt > $today->modify('+' . MAX_ADVANCE_DAYS . ' days')) {
                $err['date'] = 'Tickets can only be booked up to ' . MAX_ADVANCE_DAYS . ' days ahead.';
            } else {
                $data['date'] = $date;
            }
        }

        $n = filter_var($src['n'] ?? null, FILTER_VALIDATE_INT);
        if ($n === false || $n < 1 || $n > MAX_PASSENGERS) {
            $err['n'] = 'Passengers must be between 1 and ' . MAX_PASSENGERS . '.';
        } else {
            $data['n'] = $n;
        }

        $class = $src['class'] ?? '';
        if ($class !== '') {
            if (!is_string($class) || !in_array($class, $this->model->classes(), true)) {
                $err['class'] = 'Unknown train class.';
            } else {
                $data['class'] = $class;
            }
        }

        if ($needSchedule) {
            $sid = filter_var($src['schedule_id'] ?? null, FILTER_VALIDATE_INT);
            if ($sid === false || $sid < 1) {
                $err['schedule_id'] = 'Choose a train.';
            } else {
                $data['schedule_id'] = $sid;
            }
        }
        return [$data, $err];
    }

    /** Data for train-search.php */
    public function searchPage(array $get, int $userId): array
    {
        $submitted = isset($get['from']) || isset($get['to']);
        $stations  = $this->model->stations();
        $classes   = $this->model->classes();

        [$q, $errors] = $submitted
            ? $this->parseJourney($get)
            : [['from' => 0, 'to' => 0, 'date' => date('Y-m-d', strtotime('+1 day')), 'n' => 1, 'class' => ''], []];

        $maxFare = filter_var($get['max_fare'] ?? 150, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10, 'max_range' => 150]]);
        $maxFare = $maxFare === false ? 150 : $maxFare;
        $sort    = in_array($get['sort'] ?? '', ['dep', 'price', 'dur'], true) ? $get['sort'] : 'dep';

        $results = [];
        $dbError = false;
        if ($submitted && !$errors) {
            try {
                foreach ($this->model->search($q['from'], $q['to'], $q['date'], $userId) as $train) {
                    $train['options'] = array_filter($train['options'], function ($o) use ($q, $maxFare) {
                        return ($q['class'] === '' || $o['class'] === $q['class']) && $o['fare'] <= $maxFare;
                    });
                    if ($train['options']) {
                        $train['min_fare'] = min(array_column($train['options'], 'fare'));
                        $results[] = $train;
                    }
                }
                usort($results, fn($a, $b) => match ($sort) {
                    'price' => $a['min_fare'] <=> $b['min_fare'],
                    'dur'   => $a['mins'] <=> $b['mins'],
                    default => strcmp($a['dep'], $b['dep']),
                });
            } catch (PDOException $ex) {
                error_log('SeatController::searchPage ' . $ex->getMessage());
                $dbError = true;
            }
        }

        return [
            'submitted'   => $submitted, 'q' => $q, 'errors' => $errors,
            'stations'    => $stations, 'classes' => $classes,
            'stationName' => array_column($stations, 'name', 'station_id'),
            'maxFare'     => $maxFare, 'sort' => $sort,
            'results'     => $results, 'dbError' => $dbError,
        ];
    }

    /** Data for seat-selection.php */
    public function seatPage(array $get, int $userId): array
    {
        $flashError = $_SESSION['seat_error'] ?? null;
        unset($_SESSION['seat_error']);

        $fatal = null;
        $seg = $classFares = $coaches = null;
        [$q, $errors] = $this->parseJourney($get, true);
        $stations = array_column($this->model->stations(), 'name', 'station_id');

        if ($errors || $q['class'] === '') {
            $fatal = 'This seat selection link is not valid. Please search again.';
        } else {
            try {
                $seg = $this->model->segment($q['schedule_id'], $q['from'], $q['to']);
                if (!$seg) {
                    $fatal = 'This train is not available for your journey. It may have departed or been cancelled.';
                } else {
                    $q['date'] = substr($seg['dep'], 0, 10);
                    $classFares = $this->model->classPrices((int) $seg['train_id'], (float) $seg['seg_fare']);
                    if (!isset($classFares[$q['class']])) {
                        $fatal = 'That class is not available on this train.';
                    } else {
                        $coaches = $this->model->seatMap(
                            $q['schedule_id'], (int) $seg['train_id'], $q['class'],
                            (int) $seg['fs'], (int) $seg['ts'], $userId
                        );
                    }
                }
            } catch (PDOException $ex) {
                error_log('SeatController::seatPage ' . $ex->getMessage());
                $fatal = 'We could not load the seat map right now. Please try again in a moment.';
            }
        }
        return compact('q', 'errors', 'stations', 'fatal', 'flashError', 'seg', 'classFares', 'coaches');
    }

    /** Handles the seat form. Returns the URL to redirect to. */
    public function hold(array $post, int $userId): string
    {
        [$q, $errors] = $this->parseJourney($post, true);
        if ($errors || $q['class'] === '') {
            http_response_code(400);
            exit('Invalid request. Please search again.');
        }
        $back = 'seat-selection.php?' . http_build_query([
            'schedule_id' => $q['schedule_id'], 'date' => $q['date'], 'from' => $q['from'],
            'to' => $q['to'], 'n' => $q['n'], 'class' => $q['class'],
        ]);
        $fail = function (string $msg, string $url): string {
            $_SESSION['seat_error'] = $msg;
            return $url;
        };

        $raw = $post['seats'] ?? [];
        if (!is_array($raw)) {
            return $fail('Select your seats again.', $back);
        }
        $ids = [];
        foreach ($raw as $v) {
            $id = filter_var($v, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                return $fail('Select your seats again.', $back);
            }
            $ids[$id] = $id;
        }
        $ids = array_values($ids);
        if (count($ids) !== $q['n']) {
            return $fail('Please select exactly ' . $q['n'] . ' seat(s) for ' . $q['n'] . ' passenger(s).', $back);
        }

        $res = $this->model->holdSeats($userId, $q['schedule_id'], $q['from'], $q['to'], $q['class'], $ids);
        if (!$res['ok']) {
            return $fail($res['message'], $res['to'] === 'search' ? 'train-search.php' : $back);
        }

        // Hand-over to M3. NO price is passed: M3 must calculate the fare on the server
        // (see SeatModel::fareFor).
        $_SESSION['booking_draft'] = [
            'schedule_id'     => $q['schedule_id'],
            'from_station_id' => $q['from'],
            'to_station_id'   => $q['to'],
            'from_seq'        => (int) $res['segment']['fs'],
            'to_seq'          => (int) $res['segment']['ts'],
            'class'           => $q['class'],
            'passengers'      => $q['n'],
            'seat_ids'        => $ids,
            'seat_labels'     => $res['labels'],
            'hold_minutes'    => HOLD_MINUTES,
            'held_at'         => time(),
        ];
        return URL_NEXT;
    }
}
