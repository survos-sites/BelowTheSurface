<?php

namespace App\Controller;

use App\Controller\Admin\DashboardController;
use App\Controller\Admin\MeiliDashboardController;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerHelper;
use Symfony\Component\DependencyInjection\Attribute\AutowireMethodOf;

class AppController
{
	public function __construct(
        // this is mostly to play with a controller that doesn't extend AbstractController.
        // https://symfony.com/blog/new-in-symfony-7-4-decoupled-controller-helpers#introducing-controllerhelper
        #[AutowireMethodOf(ControllerHelper::class)]
        private \Closure $redirectToRoute,
    )
	{
	}


	#[Route(path: '/home', name: 'app_homepage')]
	#[Template('app/app_homepage.html.twig')]
	public function app_homepage(Request $request): array|Response
	{
		return [];
	}

    #[Route('/', name: 'app_redirect')]
    final public function redirectToMeili(Request $request): RedirectResponse
    {
        $locale = $request->getPreferredLanguage(['en', 'nl', 'fr']) ?? 'en';

        return ($this->redirectToRoute)(MeiliDashboardController::MEILI_ROUTE, ['_locale' => $locale]);
    }
}
