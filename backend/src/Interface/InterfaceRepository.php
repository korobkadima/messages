<?php

declare(strict_types=1);

namespace App\Interface;
use App\Entity\Message;
use Symfony\Component\HttpFoundation\Request;

interface InterfaceRepository
{
    /**
     * @param Request $request
     */
    public function getPaginate(Request $request): array;


    /**
     * @param Request $request
     * @param string $uuid
     * @return array
     */
    public function findOneByUuid(Request $request, string $uuid = ''): array;

    /**
     * @param Message $message
     * @return Message
     */
    public function save(Message $message): Message;
}
