<?php

namespace App\Service;

use App\Entity\Picture;
use App\Entity\Trick;
use App\Entity\Video;
use App\Repository\PictureRepository;
use App\Repository\VideoRepository;

class MediaService
{

    public function __construct(private PictureRepository $pictureRepository, private VideoRepository $videoRepository)
    {
    }

    public function updateMediaEntity(?string $media, ?string $url): bool
    {
        if (empty($media) || empty($url)) return true;
        $mediaArray = explode(":", $media);
        return match ($mediaArray[0]) {
            "picture" => $this->pictureRepository->updatePictureById($mediaArray[1], $url),
            "video" => $this->videoRepository->updateVideoById($mediaArray[1], $url),
        };
    }

    /**
     * @param Trick $trick
     * @param array $additionalMedia
     * @param array $trickForm
     * @return void
     */
    public function addedNewMedia(Trick $trick, array $additionalMedia, array $trickForm)
    {
        //we create the first picture who's mandatory
        $pictureEntity = new Picture();
        $pictureEntity->setLink($trickForm["picture"])
            ->setTrick($trick);
        $this->pictureRepository->add($pictureEntity, true);

        //we create the first video, who's also mandatory
        $videoEntity = new Video();
        $videoEntity->setLink($trickForm["video"])
            ->setTrick($trick);
        $this->videoRepository->add($videoEntity, true);

        foreach ($additionalMedia["picture"] as $picture) {
            (new Picture())->setLink($picture)
                ->setTrick($trick);
        }
        foreach ($additionalMedia["video"] as $video) {
            (new Video())->setLink($video)
                ->setTrick($trick);
        }

    }

    /**
     * @param string $typeMedia
     * @param int $idMedia
     * @return string[]|void
     */
    public function deleteMedia(string $typeMedia, int $idMedia)
    {

        //we make sure its not the last media of the category, because its impossible to have 0 picture or 0 video
        if ($typeMedia === "picture") {
            $pictureEntity = $this->pictureRepository->find($idMedia);
            if ($pictureEntity) {
                $trickEntity = $pictureEntity->getTrick();
                if (count($trickEntity->getPicture()) > 1) {
                    $this->pictureRepository->remove($pictureEntity, true);
                    return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_SUCCESS, "La photo ?? bien ??t?? supprim?? !");
                }
                return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_DANGER, "/!\ Impossible de supprimer cette photo, car la figure doit poss??der au minimum une photo !");
            }
            return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_WARNING, "/!\ Cette image n'existe pas, ou a d??ja ??t?? supprim?? !");
        } elseif ($typeMedia === "video") {
            $videoEntity = $this->videoRepository->find($idMedia);
            if ($videoEntity) {
                $trickEntity = $videoEntity->getTrick();
                if (count($trickEntity->getPicture()) > 1) {
                    $this->videoRepository->remove($videoEntity, true);
                    return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_SUCCESS, "La vid??o a bien ??t?? supprim?? !");
                }
                return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_DANGER, "Impossible de supprimer cette vid??o, car la figure doit poss??der au minimum une vid??o !");
            }
            return FlashService::getFlashArray(FlashService::MESSAGE_TYPE_WARNING, "/!\ Cette vid??o n'existe pas, ou a d??ja ??t?? supprim?? !");
        }
    }

    public function addPictureEntity(Picture $picture)
    {
        $this->pictureRepository->add($picture, true);
    }

    public function addVideoEntity(Video $video)
    {
        $this->videoRepository->add($video, true);
    }
}

