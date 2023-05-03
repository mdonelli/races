<?php

namespace App\Controller;

use App\Entity\Race;
use App\Entity\RaceResult;
use App\Repository\RaceRepository;
use App\Repository\RaceResultRepository;
use App\Service\RaceService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('race')]
class RaceController extends AbstractController
{
    protected RaceService $raceService;
    public function __construct(RaceService $raceService)
    {
        $this->raceService = $raceService;
    }

    #[Route('/import', name: 'import')]
    public function import(Request $request, Filesystem $filesystem) :JsonResponse
    {
        $data = $request->request->all();
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        if (empty($data['title']) || empty($data['date']) || empty($file)) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->raceService->importFromFile($data['title'], $data['date'], $file->getRealPath());
        $filesystem->remove($file);

        return new JsonResponse([], $result ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    #[Route('/collection', name: 'raceCollection')]
    public function getRaceCollection(Request $request) :JsonResponse
    {
        try {
            return new JsonResponse($this->raceService->getRaceCollection($request->query->all()));
        } catch (\Exception $exception) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/results/{id}', name: 'raceResults')]
    #[ParamConverter('race', class: Race::class)]
    public function getRaceResultsForRace(Request $request, ?Race $race) :JsonResponse
    {
        if (empty($race)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        try {
            return new JsonResponse($this->raceService->getResultsByRace($race, $request->query->all()));
        } catch (\Exception $exception) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/results-ids/{id}', name: 'raceResultsWithIds')]
    #[ParamConverter('race', class: Race::class)]
    public function getRaceResults(Request $request, ?Race $race) :JsonResponse
    {
        if (empty($race)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        try {
            return new JsonResponse($this->raceService->getResults($race));
        } catch (\Exception $exception) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/edit-result/{id}', name: 'editRaceResult')]
    #[ParamConverter('raceResult', class: RaceResult::class)]
    public function editRaceResult(Request $request, ?RaceResult $raceResult) :JsonResponse
    {
        if (empty($raceResult)) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }
        try {
            $values = json_decode($request->getContent(), true);
            if (empty($values) || empty($values['full_name']) || empty($values['time']) || empty($values['distance']) || empty($values['age_category'])) {
                return new JsonResponse([], Response::HTTP_BAD_REQUEST);
            }

            $this->raceService->editResult($raceResult, $values);

            return new JsonResponse([]);

        } catch (\Exception $exception) {
            return new JsonResponse([], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/all', name: 'getRaces')]
    public function getRaces() :JsonResponse {
        return new JsonResponse($this->raceService->getRaces());
    }
}