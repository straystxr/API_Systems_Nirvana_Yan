<?php

class User {

    private $conn;
    private $table = "users";
    private $alias = "u";

    public $id;
    public $password;

    public function __construct($db){
        $this->conn = $db;
    }

    // GET USER
    public function readSingle(){

        $query = "
            SELECT
                {$this->alias}.id,
                {$this->alias}.username,
                {$this->alias}.display_name,
                {$this->alias}.email,
                {$this->alias}.user_type,
                {$this->alias}.profile_photo_url,
                {$this->alias}.is_verified,
                {$this->alias}.created_at,
                {$this->alias}.membership_id
            FROM {$this->table} AS {$this->alias}
            WHERE {$this->alias}.id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        return $stmt;
    }

    // UPDATE PASSWORD
    public function updatePassword(){

        $query = "
            UPDATE {$this->table}
            SET password_hash = :password
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($query);

        $hashedPassword = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(":password", $hashedPassword);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // DELETE USER
    public function delete(){

        $query = "DELETE FROM {$this->table} WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }
}


// ─────────────────────────────────────────────
// ROUTER
// ─────────────────────────────────────────────

function handleUsers(string $method, string $userId, string $action){

    if (!$userId) {
        error('User ID required', 400);
    }

    $db = getDB();
    $user = new User($db);
    $user->id = $userId;

    // GET USER
    if ($action === '' && $method === 'GET') {

        $stmt = $user->readSingle();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            error('User not found', 404);
        }

        respond([
            'user' => [
                'id'            => (int)$data['id'],
                'name'          => $data['display_name'],
                'username'      => $data['username'],
                'email'         => $data['email'],
                'role'          => $data['user_type'],
                'joined'        => $data['created_at'],
                'membership_id'=> $data['membership_id'],
                'is_verified'   => (bool)$data['is_verified']
            ]
        ]);
        return;
    }

    // UPDATE PASSWORD
    if ($action === 'password' && $method === 'PATCH') {

        $body = body();

        if (empty($body['newPass'])) {
            error('New password required', 400);
        }

        $user->password = $body['newPass'];

        if ($user->updatePassword()) {
            respond(['message' => 'Password updated successfully']);
        }

        error('Failed to update password', 500);
    }

    // DELETE USER (ADMIN ONLY)
    if ($action === '' && $method === 'DELETE') {

        $authUser = verifyToken();

        if (($authUser['role'] ?? '') !== 'admin') {
            error('Admin only', 403);
        }

        if ($user->delete()) {
            respond(['message' => 'User deleted successfully']);
        }

        error('Failed to delete user', 500);
    }

    error('Endpoint not found', 404);
}