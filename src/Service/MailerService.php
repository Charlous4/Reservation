<?php

namespace App\Service;

use App\Entity\Session;
use App\Entity\Membre;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    public function sendConfirmation(Membre $membre, Session $session): void
    {
        $email = (new Email())
            ->from('noreply@centresportif.fr')
            ->to($membre->getEmail())
            ->subject('Confirmation de votre réservation')
            ->html("
                <h2>Réservation confirmée ✅</h2>
                <p>Bonjour {$membre->getPrenom()},</p>
                <p>Votre réservation pour <strong>{$session->getActivite()->getNom()}</strong> a bien été enregistrée.</p>
                <p>📅 Le {$session->getDateDeb()->format('d/m/Y')} de {$session->getHeureDeb()->format('H:i')} à {$session->getHeureFin()->format('H:i')}</p>
                <p>À bientôt !</p>
            ");

        $this->mailer->send($email);
    }

    public function sendAnnulation(Membre $membre, Session $session): void
    {
        $email = (new Email())
            ->from('noreply@centresportif.fr')
            ->to($membre->getEmail())
            ->subject('Annulation de votre réservation')
            ->html("
                <h2>Réservation annulée ❌</h2>
                <p>Bonjour {$membre->getPrenom()},</p>
                <p>Votre réservation pour <strong>{$session->getActivite()->getNom()}</strong> a bien été annulée.</p>
                <p>À bientôt !</p>
            ");

        $this->mailer->send($email);
    }

    public function sendGroupEmail(array $membres, string $sujet, string $contenu): void
    {
        foreach ($membres as $membre) {
            $email = (new Email())
                ->from('noreply@centresportif.fr')
                ->to($membre->getEmail())
                ->subject($sujet)
                ->html("
                    <p>Bonjour {$membre->getPrenom()},</p>
                    <p>{$contenu}</p>
                    <p>L'équipe du centre sportif</p>
                ");

            $this->mailer->send($email);
            sleep(2); //  2 seconde entre chaque mail
        }
    }

    public function sendRappel(Membre $membre, Session $session): void
{
    $email = (new Email())
        ->from('noreply@centresportif.fr')
        ->to($membre->getEmail())
        ->subject('Rappel : votre session demain !')
        ->html("
            <h2>Rappel de session 📅</h2>
            <p>Bonjour {$membre->getPrenom()},</p>
            <p>On vous rappelle que vous avez une session <strong>{$session->getActivite()->getNom()}</strong> demain !</p>
            <p>🕐 De {$session->getHeureDeb()->format('H:i')} à {$session->getHeureFin()->format('H:i')}</p>
            <p>À demain !</p>
        ");

    $this->mailer->send($email);
}
}