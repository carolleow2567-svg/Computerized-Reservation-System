
<?php

class Passenger
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function findById($id)
    {
        $sql = "SELECT id, fullname, email, phone, identity, created_at
                FROM passengers
                WHERE id = :id
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetch();
    }

    public function updateProfile($id, $fullname, $email, $phone, $identity)
    {
        $sql = "UPDATE passengers
                SET fullname = :fullname,
                    email = :email,
                    phone = :phone,
                    identity = :identity
                WHERE id = :id";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'fullname' => $fullname,
            'email' => $email,
            'phone' => $phone,
            'identity' => $identity,
            'id' => $id
        ]);
    }
}
