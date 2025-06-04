<?php

namespace App\Service;

use App\Entity\Admin;
use App\Entity\Answer;
use App\Entity\Client;
use App\Entity\ClientTester;
use App\Entity\Tester;
use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadService
{
    private ContainerBagInterface $containerBag;
    private ParameterBagInterface $parameterBag;

    public function __construct(ContainerBagInterface $containerBag,ParameterBagInterface $parameterBag)
    {
        $this->containerBag = $containerBag;
        $this->parameterBag = $parameterBag;
    }
    public function uploadProfileImage($file, User|ClientTester|Tester|Admin|Client $user): false|string
    {
        try {
            $filename = $user->getId().'_'.uniqid()."." . $file->getClientOriginalExtension();
            if ($file->move($this->parameterBag->get('profile_pic_path'),$filename))
            {
                return $this->parameterBag->get('profile_pic_path').$filename;
            }
            return $filename;
        }catch(\Exception $exception)
        {
            return false;
        }
    }
    public function uploadTestFaceShotsImage($base64String): false|string
    {
        try {
            $base64String = preg_replace('/^data:image\/\w+;base64,/', '', $base64String);
            $imageData = base64_decode($base64String);
            $tempFilePath = $this->parameterBag->get('faceshots_path').uniqid().'.jpg';
            file_put_contents($tempFilePath, $imageData);
            return $tempFilePath;
        }catch(\Exception $exception)
        {
            return false;
        }
    }
}