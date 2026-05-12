<?php

namespace App\Command;

use App\Repository\SessionRepository;
use App\Service\MailerService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:rappel-sessions')]
class RappelSessionCommand extends Command
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private MailerService $mailerService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $demain = new \DateTime('tomorrow');
        $sessions = $this->sessionRepository->findAll();

        foreach ($sessions as $session) {
            if ($session->getDateDeb()->format('Y-m-d') === $demain->format('Y-m-d')) {
                foreach ($session->getInscrires() as $inscrire) {
                    $membre = $inscrire->getMembre();
                    $this->mailerService->sendRappel($membre, $session);
                    sleep(2);
                }
            }
        }

        $output->writeln('Rappels envoyés !');
        return Command::SUCCESS;
    }
}