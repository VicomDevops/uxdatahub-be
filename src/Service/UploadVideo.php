<?php

namespace App\Service;

use App\Entity\Client;
use App\Entity\Test;
use App\Entity\Tester;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadVideo
{
    private $containerBag;
    private $parameterBag;

    public function __construct(ContainerBagInterface $containerBag,ParameterBagInterface $parameterBag)
    {
        $this->containerBag = $containerBag;
        $this->parameterBag = $parameterBag;
    }

    public function upload($video, Test $test)
    {
        try {
            $filename = $test->getId(). "." . $video->getClientOriginalExtension();
            if ($video->move($this->parameterBag->get('video_path'),$filename)) {
                return $filename;
            }
            return false;
        }catch(\Exception $exception)
        {
            return false;
        }
    }

    public function uploadIdentityCard($file,User $user, $fileName="front")
    {
        $extension = pathinfo($file['name'])['extension'];
        $filename = $this->containerBag->get('kernel.project_dir')."/public/2m/insight-data/identity-card/".$user->getId(). "-" .$fileName. "." . $extension;
        if (move_uploaded_file($file['tmp_name'], $filename)) {
            return $filename;
        }
	return false;

    }

    
    public function uploadProfileImage($file,User $user)
    {
        $extension = pathinfo($file['name'])['extension'];
        $filename = $this->containerBag->get('kernel.project_dir')."/public/2m/insight-data/profile-image/".$user->getId(). "." . $extension;
        if (move_uploaded_file($file['tmp_name'], $filename)) {
            return $filename;
        }
	return false;

    }

    
    public function uploadContractFile($file, Client $client)
    {   
	$extension = pathinfo($file['name'])['extension'];
	$filename = $this->containerBag->get('kernel.project_dir')."\public\\2m\insight-data\contract\\".$client->getId().".". $extension;
	if (move_uploaded_file($file['tmp_name'], $filename)) {
            return $filename;
        }
	return false;

    }

}
