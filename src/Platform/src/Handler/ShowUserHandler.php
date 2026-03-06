<?php

declare(strict_types=1);

namespace Platform\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Template\TemplateRendererInterface;
use Neutrino\Domain\User\User;
use Neutrino\Repository\UserActivityRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

final readonly class ShowUserHandler implements RequestHandlerInterface
{
    public function __construct(
        private TemplateRendererInterface $template,
        private EntityManagerInterface $em,
        private UserActivityRepository $activityRepository
    ) {
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $id = $request->getAttribute('id');

        $user = $this->em->find(User::class, $id);

        if (!$user) {
            // throw new NotFoundException(sprintf('User with id "%s" not found', $id));
            throw new RuntimeException(sprintf('User with id "%s" not found', $id));
        }

        // Fetch recent user activities (limit to 10 most recent)
        $activities = $this->activityRepository->findRecentByUser($user, 10);

        $content = $this->template->render('platform::account/view', [
            'user' => $user,
            'activities' => $activities,
        ]);

        return new HtmlResponse($this->template->render('layout::platform', [
            'user'    => $user,
            'content' => $content,
        ]));
    }
}
