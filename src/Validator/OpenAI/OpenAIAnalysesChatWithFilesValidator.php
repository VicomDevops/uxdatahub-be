<?php

namespace App\Validator\OpenAI;

use Symfony\Component\Validator\Constraints as Assert;

class OpenAIAnalysesChatWithFilesValidator
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

    /**
     * @Assert\NotBlank(message="The URL must not be blank.")
     * @Assert\Url(message="The URL provided is not valid.")
     */
    private $url;

    /**
     * @Assert\NotBlank(message="The work field must not be blank.")
     * @Assert\Length(
     *     max=255,
     *     maxMessage="The work field must not exceed 255 characters."
     * )
     */
    private $workField;

    public function __construct(array $params)
    {
        $this->file = @$params['file'];
        $this->url = @$params['url'];
        $this->workField = @$params['workField'];
    }
}