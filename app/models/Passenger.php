
<?php

class Passenger
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Find passenger from users table
    public function findById($id)
    {
        $sql = "SELECT
            user_id AS id,
            full_name AS fullname,
            email,
            phone_number AS phone,
            role,
            created_at
        FROM users
        WHERE user_id = :id
        AND role = 'passenger'
        LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    // Update passenger profile
    public function updateProfile($id, $fullname, $email, $phone)
    {
        $sql = "UPDATE users
                SET full_name = :fullname,
                    email = :email,
                    phone_number = :phone
                WHERE user_id = :id
                AND role = 'passenger'";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'id' => $id
        ]);
    }
}
