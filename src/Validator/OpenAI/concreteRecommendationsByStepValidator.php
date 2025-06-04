<?php

namespace App\Validator\OpenAI;

use Symfony\Component\Validator\Constraints as Assert;

class concreteRecommendationsByStepValidator
{
    /**
     * @Assert\NotBlank()
     * @Assert\File(
     *     maxSize = "5M",
     *     mimeTypes = {
     *         "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
     *     },
     *     mimeTypesMessage = "Please upload a valid PDF, image, Excel, or Word file."
     * )
     */
    private $file;

    public function __construct(array $params)
    {
        $this->file = @$params['file'];
    }

}