
<?php

class User
{
    private PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // Find a passenger by email
    public function findPassengerByEmail(string $email): ?array
    {
        $sql = "SELECT *
                FROM users
                WHERE email = :email
                AND role = 'passenger'
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch();

        return $user ?: null;
    }
}
