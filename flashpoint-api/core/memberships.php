<?php

class Membership {

    // DATABASE
    private $conn;
    private $table = "memberships";
    private $alias = "m";

    // PROPERTIES
    public $user_id;
    public $tier_id;

    // CONSTRUCTOR
    public function __construct($db){
        $this->conn = $db;
    }

    // GET MEMBERSHIP
    public function read(){

        $query = "
            SELECT
                mt.*,
                {$this->alias}.id AS membership_id
            FROM {$this->table} AS {$this->alias}
            LEFT JOIN membershipTiers mt
                ON {$this->alias}.tier_id = mt.id
            WHERE {$this->alias}.user_id = ?
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(1, $this->user_id);

        $stmt->execute();

        return $stmt;
    }

    // UPDATE MEMBERSHIP
    public function update(){

        $query = "
            UPDATE {$this->table}
            SET tier_id = :tier_id
            WHERE user_id = :user_id
        ";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":tier_id", $this->tier_id);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }
}



// ─────────────────────────────────────────────
// ROUTER FUNCTION
// ─────────────────────────────────────────────

function handleMemberships(string $method, string $userId, string $action){

    $db = getDB();

    $membership = new Membership($db);

    $membership->user_id = $userId;

    // GET MEMBERSHIP
    if($action === 'membership' && $method === 'GET'){

        $stmt = $membership->read();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$data){
            error('Membership not found', 404);
        }

      respond([
         'membership' => [
        'tier_id'                       => $data['id'],
        'name'                          => $data['name'],
        'price_eur'                     => $data['price_eur'],
        'description'                   => $data['description'],
        'can_remove_ads'                => (bool)$data['can_remove_ads'],
        'can_upload_media'              => (bool)$data['can_upload_media'],
        'can_post_news'                 => (bool)$data['can_post_news'],
        'can_bookmark'                  => (bool)$data['can_bookmark'],
        'can_comment'                   => (bool)$data['can_comment'],
        'can_react'                     => (bool)$data['can_react'],
        'can_get_discounts'             => (bool)$data['can_get_discounts'],
        'can_access_vacancies'          => (bool)$data['can_access_vacancies'],
        'can_receive_fast_notifications'=> (bool)$data['can_receive_fast_notifications'],
        'can_view_videos_early'         => (bool)$data['can_view_videos_early'],
        'membership_id'                 => $data['membership_id']
            ]
            ]);
        return;
    }

    // CHANGE MEMBERSHIP
    if($action === 'membership' && $method === 'PATCH'){

        $body = body();

        if(empty($body['tier_id'])){
            error('tier_id is required', 400);
        }

        $membership->tier_id = $body['tier_id'];

        if($membership->update()){

            respond([
                'message' => 'Membership updated successfully'
            ]);

        } else {

            error('Failed to update membership', 500);
        }

        return;
    }

    error('Endpoint not found', 404);
}