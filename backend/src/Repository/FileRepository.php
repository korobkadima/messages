<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Message;
use App\Interface\InterfaceRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final class FileRepository implements InterfaceRepository
{
    const MESSAGES_FILE = __DIR__ . '/../../storage/messages.json';

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getPaginate(Request $request): array
    {
        $offset = (int) $request->get('skip');
        $limit = (int) $request->get('take');
        $sort = $request->get('sort');

        $messages = $this->getAllMessages();

        if (isset($sort)) {
            $sort = array_values(json_decode($sort, true));
            $sort = current($sort);
            if (in_array($sort['selector'], ['uuid', 'createdAt'])) {
                $values = array_column($messages, $sort['selector']);
                array_multisort(
                    $values,
                    $sort['desc'] ? SORT_DESC : SORT_ASC,
                    $messages
                );
            }
        }

        $messages = array_slice($messages, $offset, $limit);

        foreach ($messages as &$message) {
            $message['createdAt'] = date("m/d/Y H:i:s", $message['createdAt']);
        }

        return $messages;
    }

    /**
     * @param Request $request
     * @param string $uuid
     * @return array
     */
    public function findOneByUuid(Request $request, string $uuid = ''): array
    {
        $data = $this->getAllMessages($request);

        $message = current(array_filter($data, function($row) use ($uuid) {
            return $row['uuid'] == $uuid;
        }));

        $message['createdAt'] = date("m/d/Y H:i:s", $message['createdAt']);

        return $message;
    }

    /**
     * @param Message $message
     * @return Message
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function save(Message $message): Message
    {
        $messages = $this->getAllMessages();
        $messages[] = $this->serializer->normalize($message);
        $messages = json_encode($messages, JSON_PRETTY_PRINT);

        file_put_contents(self::MESSAGES_FILE, $messages);

        return $message;
    }

    /**
     * @return array
     */
    public function getAllMessages(): array
    {
        if (!$this->filesystem->exists(self::MESSAGES_FILE)) {
            $this->filesystem->dumpFile(self::MESSAGES_FILE, json_encode([]));
        }

        $data = file_get_contents(self::MESSAGES_FILE);
        if (strlen($data) === 0) {
            $data = json_encode([]);
        }

        return json_decode($data, true);
    }
}
