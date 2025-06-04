<?php

namespace App\DataFixtures;

class DataFixtures
{
    public function getDefaultUsers()
    {
        return  array(
            array('id' => 1,'email' => 'admin@admin.com', 'roles' => ["ROLE_ADMIN"] ,'password' => 'admin123','isActive' => true, 'isFirstConnection' => false,
                'created_at' => '2024-01-18 10:27:05','state' => 'to_contact','username' => 'admin', "name" => "Admin","lastname" => "admin"),

            array('id' => 2,'email' => 'tester@tester.com','roles' => ["ROLE_TESTER"],'password' => 'tester123','isActive' => true, 'isFirstConnection' => false,
                'created_at' => '2024-01-18 10:27:05','state' => 'to_contact','username' => 'tester', "name" => "tester","lastname" => "tester",
                "gender" => "Male","country" => "France","csp" => "Artisans","dateOfBirth" =>"2024-01-18 10:27:05","study_level" => "Activités informatiques",
                "socialMedia" => "https://www.linkedin.com/feed/","os" =>"Android","maritalStatus" => "Célibataire", "phone" => "+21624186422"),

            array('id' => 3,'email' => 'tester@tester.fr','roles' => ["ROLE_TESTER"],'password' => 'tester123','isActive' => true, 'isFirstConnection' => false,
                'created_at' => '2024-01-18 10:27:05','state' => 'to_contact','username' => 'tester', "name" => "tester","lastname" => "tester",
                "gender" => "Male","country" => "France","csp" => "Artisans","dateOfBirth" =>"2024-01-18 10:27:05","study_level" => "Activités informatiques",
                "socialMedia" => "https://www.linkedin.com/feed/","os" => "Android", "maritalStatus" => "Célibataire", "phone" => "+21624186422"),

            array('id' => 4,'email' => 'client@client.com','roles' => ["ROLE_CLIENT"],'password' => 'client123','isActive' => true, 'isFirstConnection' => false,
                'created_at' => '2024-01-18 10:27:05','state' => 'to_contact','username' => 'client', "name" => "client","lastname" => "client","phone" => "+21624186422",
                "company" => "Labsoft","profession" => "Client", "sector" => "IT","nbEmployees" => "2","useCase" => "Entreprise: Projet Ponctuel"),
        );
    }

}