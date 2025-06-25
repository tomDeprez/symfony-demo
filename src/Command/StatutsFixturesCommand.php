<?php

namespace App\Command;

use App\Entity\Statuts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fixtures:statuts',
    description: 'Ajoute ou met à jour les statuts en base de données.',
)]
class StatutsFixturesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $statutsData = [
            ['Confirmée', 'Commande confirmée', '#28a745'],
            ['Non traité', 'Commande non encore traitée', '#dc3545'],
            ['Partiellement traité', 'Commande partiellement traitée', '#ffc107'],
            ['Programmée', 'Commande programmée pour une date ultérieure', '#17a2b8'],
            ['En attente', 'Commande en attente de traitement', '#6c757d'],
        ];

        $repo = $this->em->getRepository(Statuts::class);

        foreach ($statutsData as [$libelle, $description, $color]) {
            $statut = $repo->findOneBy(['Statut' => $libelle]);

            if (!$statut) {
                $statut = new Statuts();
                $statut->setStatut($libelle);
            }

            $statut->setDescription($description)
                   ->setColor($color);

            $this->em->persist($statut);
        }

        $this->em->flush();

        $output->writeln('<info>Les statuts ont été ajoutés ou mis à jour avec succès.</info>');

        return Command::SUCCESS;
    }
}
