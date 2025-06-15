<?php

namespace App\Service;

use App\Entity\ClientTester;
use Doctrine\ORM\EntityManagerInterface;

class CsvToClientTesters
{
    public function createClientTester($filename, $panel, EntityManagerInterface $entityManager)
    {
        $row = 1;
        if (($handle = fopen($filename, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if ($row == 1) {
                    $row++;
                    continue;
                }
                $row++;
                $tester = new ClientTester();
                $tester->setName($data[0])
                    ->setLastname($data[1])
                    ->setEmail($data[2])
                    ->addPanel($panel)
                ;
                $entityManager->persist($tester);
            }
            fclose($handle);
        }

        return true;

    }

}