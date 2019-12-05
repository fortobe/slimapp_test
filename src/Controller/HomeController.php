<?php

declare(strict_types=1);

namespace App\Controller;

use App\Command\FetchDataCommand;
use App\Entity\Movie;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Http\Adapter\Guzzle6\Client;
use Http\Client\Common\HttpClientRouter;
use Monolog\Logger;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Interfaces\RouteCollectorInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tests\Output\TestOutput;
use Twig\Environment;

/**
 * Class HomeController.
 */
class HomeController
{
    /**
     * @var RouteCollectorInterface
     */
    private $routeCollector;

    /**
     * @var Environment
     */
    private $twig;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * HomeController constructor.
     *
     * @param RouteCollectorInterface $routeCollector
     * @param Environment             $twig
     * @param EntityManagerInterface  $em
     */
    public function __construct(RouteCollectorInterface $routeCollector, Environment $twig, EntityManagerInterface $em)
    {
        $this->routeCollector = $routeCollector;
        $this->twig = $twig;
        $this->em = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     *
     * @throws HttpBadRequestException
     */
    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $data = $this->twig->render('home/index.html.twig', [
                'trailers' => $this->fetchData(),
                'info' => $this->getInfo(__FUNCTION__),
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    public function trailers(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        try {
            $trailer = $this->em->getRepository(Movie::class)->findOneBy(['id' => $args['id']]);
            $data = $this->twig->render('trailers/trailer.html.twig', [
                'trailer' => [
                    'title' => $trailer->getTitle(),
                    'poster' => $trailer->getImage(),
                    'description' => $trailer->getDescription(),
                    'link' => $trailer->getLink(),
                ],
                'info' => $this->getInfo(__FUNCTION__),
            ]);
        } catch (\Exception $e) {
            throw new HttpBadRequestException($request, $e->getMessage(), $e);
        }

        $response->getBody()->write($data);

        return $response;
    }

    /**
     * @return Collection
     */
    protected function fetchData(): Collection
    {
        $this->fetchDataFromWeb();
        $data = $this->em->getRepository(Movie::class)
            ->findAll();
        return new ArrayCollection($data);
    }

    protected function fetchDataFromWeb() {
        try {
            $fetch = new FetchDataCommand(new Client(), new Logger('movies'), $this->em, 'movies');
            $fetch->run(new ArgvInput(), new BufferedOutput());
            return true;
        } catch (\Exception $exception) {
            throw new \Error('Cannot retrieve the date from remote source: '.$exception->getMessage());
        }
    }

    private function getInfo($function = __FUNCTION__) {
        return [
            'date' => date('d.m.Y H:i:s'),
            'class' => __CLASS__, //static::class,
            'method' => $function,
        ];
    }
}
