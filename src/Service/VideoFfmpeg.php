<?php

namespace App\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Mp3;
use FFMpeg\Media\Audio;
use FFMpeg\Media\Video;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class VideoFfmpeg
{

    private $ffmpeg;
    private $containerBag;
    public function __construct(ContainerBagInterface $containerBag)
    {
        $this->containerBag=$containerBag;
        $this->ffmpeg = FFMpeg::create(array(
           'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
           'ffprobe.binaries' => '/usr/bin/ffprobe',
            'timeout' => 3600,
            'ffmpeg.threads' => 12,
        ));
    }

    public function videoToAudio($pathFile, $outputPathFile): string
    {
        $f = $this->ffmpeg->open($pathFile);
        $f->save(new Mp3(), $outputPathFile);

        return $outputPathFile;
    }

    public function clipAudio($pathFile, $start, $duration, $outputPathFile)
    {
        /** @var Audio $audio */
        $audio = $this->ffmpeg->open($pathFile);
        $audio->filters()->clip(TimeCode::fromSeconds($start), TimeCode::fromSeconds($duration));
        $audio->save(new Mp3(), $outputPathFile);

        return $outputPathFile;
    }

    public function screenShot(string $pathFile, $imagePath, int $time)
    {
        $video = $this->ffmpeg->open($pathFile);
        $fullImage = $video->frame(TimeCode::fromSeconds($time));
        $fullImagePath= $this->containerBag->get('kernel.project_dir')."\public\\2m\insight-data\screen-shots\\".rand().".jpg";
        $fullImage->save($fullImagePath);
        $image = imagecreatefromjpeg($fullImagePath);
        $im = imagecrop($image, ["x" => 0, "y" => 0, "width" => 250, "height" => 250]);
        imagejpeg($im, $imagePath);
        imagedestroy($im);
        unlink($fullImagePath);

        return $imagePath;
    }

    public function videoPerStep($pathFile,$start,$duration, $outputPathFile)
    {
        $video = $this->ffmpeg->open($pathFile);
        $clip = $video->clip(TimeCode::fromSeconds($start),TimeCode::fromSeconds($duration));
        $clip->save(new \FFMpeg\Format\Video\WebM(), $outputPathFile);
        return $outputPathFile;


    }
}