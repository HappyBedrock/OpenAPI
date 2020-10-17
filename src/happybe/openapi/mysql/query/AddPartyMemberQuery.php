<?php

declare(strict_types=1);

namespace happybe\openapi\mysql\query;

use happybe\openapi\mysql\AsyncQuery;
use mysqli;

/**
 * Class AddPartyMemberQuery
 * @package happybe\openapi\mysql\query
 */
class AddPartyMemberQuery extends AsyncQuery {

    /** @var string $owner */
    public $owner;
    /** @var string $member */
    public $member;

    /**
     * AddPartyMemberQuery constructor.
     * @param string $owner
     * @param string $member
     */
    public function __construct(string $owner, string $member) {
        $this->owner = $owner;
        $this->member = $member;
    }

    /**
     * @param mysqli $mysqli
     */
    public function query(mysqli $mysqli): void {
        $result = $mysqli->query("SELECT * FROM HB_Parties WHERE Owner='{$this->owner}';")->fetch_assoc();

        $members = explode(",", $result["Members"]);
        $members[] = $this->member;

        $mysqli->query("UPDATE HB_Parties SET Members='".implode(",", $members)."' WHERE Owner='{$this->owner}';");
    }
}