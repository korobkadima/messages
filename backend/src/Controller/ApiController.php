<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Message;
use App\Repository\FileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;

final class ApiController
{
    /**
     * @var FileRepository
     */
    private FileRepository $repository;

    /**
     * @var Request
     */
    private Request $request;

    public function __construct()
    {
        $this->repository = new FileRepository();
        $this->request = Request::createFromGlobals();
    }

    /**
     * @return JsonResponse
     */
    #[Route('/api/messages', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return (new JsonResponse([
            'data' => $this->repository->getPaginate($this->request),
            'totalCount' => count($this->repository->getAllMessages())
        ]))->send();
    }

    /**
     * @param $uuid
     * @return JsonResponse
     */
    #[Route('/api/message/{uuid}', name: 'show', methods: ['GET'])]
    public function show($uuid): JsonResponse
    {
        return (new JsonResponse($this->repository->findOneByUuid($this->request, $uuid)))->send();
    }

    /**
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/api/store', name: 'store', methods: ['POST'])]
    public function store(): JsonResponse
    {
        $message = new Message($this->request->get('message'));

        $validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();

        $errors = $validator->validate($message);
        if (count($errors) > 0) {
            $data = [
                'success' => false,
                'message' => $errors[0]->getMessage()
            ];
        } else {
            $data = [
                'success' => true,
                'uuid' => $message->getUuid()
            ];

            $this->repository->save($message);
        }

        return (new JsonResponse($data))->send();
    }
}