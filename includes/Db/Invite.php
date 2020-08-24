<?php

namespace Gaterdata\Db;

/**
 * Class Invite.
 *
 * @package Gaterdata\Db
 */
class Invite
{
    /**
     * @var int Inivite ID.
     */
    protected $iid;
    /**
     * @var string Email.
     */
    protected $created;
    /**
     * @var string Created.
     */
    protected $email;
    /**
     * @var string Invite token.
     */
    protected $token;

    /**
     * Invite constructor.
     *
     * @param int $iid
     *   Invite ID.
     * @param string $created
     *   Created date time.
     * @param string $email
     *   Invite email.
     * @param string $token
     *   Invite token.
     */
    public function __construct($iid = null, $created = null, $email = null, $token = null)
    {
        $this->iid = $iid;
        $this->created = $created;
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Get the invite ID.
     *
     * @return int
     *   Invite ID.
     */
    public function getIid()
    {
        return $this->iid;
    }

    /**
     * Set the invite ID.
     *
     * @param int $iid
     *   Invite ID.
     */
    public function setIid($iid)
    {
        $this->iid = $iid;
    }

    /**
     * Get the created date.
     *
     * @return string
     *   Created date time.
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the created date time.
     *
     * @param string $created
     *   Created date time.
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get the invite email.
     *
     * @return string
     *   Invite email.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the invite email.
     *
     * @param string $email
     *   Invite email.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get the invite token.
     *
     * @return string
     *   Invite token.
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set the invite token.
     *
     * @param string $token
     *   Invite token.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Return the invite as an associative array.
     *
     * @return array
     *   Invite.
     */
    public function dump()
    {
        return [
            'iid' => $this->iid,
            'created' => $this->created,
            'email' => $this->email,
            'token' => $this->token,
        ];
    }
}
