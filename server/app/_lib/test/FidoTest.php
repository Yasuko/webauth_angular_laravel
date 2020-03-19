<?php

namespace App\_lib\test;

use App\_lib\Fido\Fido;
use Tests\TestCase;

class ProxmoxTest extends TestCase
{

    public function testGetterSetter()
    {
        $ticket = '00000000000000000000000000';
        $token  = '00000000000000000000000000';
        
        $getToken = Proxmox::ProxmoxRepository()
                ->setToken($ticket, $token)
                ->getToken();
        $this->assertSame(
            ['Ticket' => $ticket, 'CSRFToken' => $token],
            $getToken
        );
    }
}

