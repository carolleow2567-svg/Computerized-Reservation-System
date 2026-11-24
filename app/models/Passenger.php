<?php

class Passenger
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Find a passenger from the shared users table.
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

    // Update only the authenticated passenger's profile fields.
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

    // Verify the current password before replacing its stored hash.
    public function changePassword($id, $currentPassword, $newPassword)
    {
        $stmt = $this->pdo->prepare(
            "SELECT password
             FROM users
             WHERE user_id = :id
               AND role = 'passenger'
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $storedHash = $stmt->fetchColumn();

        if (!$storedHash || !password_verify($currentPassword, $storedHash)) {
            return false;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $this->pdo->prepare(
            "UPDATE users
             SET password = :password
             WHERE user_id = :id
               AND role = 'passenger'"
        );

        return $update->execute([
            'password' => $newHash,
            'id' => $id
        ]);
    }
}
