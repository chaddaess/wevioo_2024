<?php

namespace App\Controller;

use App\Entity\Event;
use App\Http\EmptyResponse;
use App\Service\BotmanService;
use App\Service\TypeSenseService;
use BotMan\BotMan\Cache\SymfonyCache;
use BotMan\Drivers\Web\WebDriver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use Symfony\Component\Routing\Attribute\Route;

class BotmanController extends AbstractController
{
    #[Route('/message',name:'message')]
    public function message(Request $request,BotmanService $botService,TypeSenseService $typeSenseService){
        DriverManager::loadDriver(WebDriver::class);
        $adapter = new FilesystemAdapter();

        $botman = BotManFactory::create([], new SymfonyCache($adapter), $request);

        $botman->hears('.*\b(hello|hi|hey|good morning)\b.*', static function (BotMan $bot) {
            $bot->typesAndWaits(1);
            $bot->reply('Hello 🤗 How can I be of assistance?');
        });

        $botman->hears('/load',static function (BotMan $bot) use ($botService) {
            $bot->typesAndWaits(1);
            $bot->reply($botService->handleLoadData());
        });
        $botman->hears('/set',static function (BotMan $bot) use ($botService) {
            $bot->typesAndWaits(1);
            $bot->reply($botService->handleCreateSchema());
        });
        $botman->hears('/search',static function (BotMan $bot,$keywords='*') use ($botService) {
            $bot->typesAndWaits(1);
            $bot->reply($botService->handleSearch($keywords));
        });
        $botman->hears('/search {keywords}',static function (BotMan $bot,$keywords) use ($botService) {
            $bot->typesAndWaits(1);
            $bot->reply($botService->handleSearch($keywords));
        });



        $botman->fallback(function (BotMan $bot) use ($botService) {
            $bot->typesAndWaits(1);
            $bot->reply($botService->handleUnknown());
        });

        $botman->listen();
        die();
    }

    #[Route('/chatframe',name:'chatframe')]
    public function chatframeAction(Request $request): Response
    {
        return $this->render('chat_frame.html.twig');
    }
}