<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

use Symfony\Component\Uid\Uuid;

class CalendarDataContainer
{
    protected string $fileName;

    protected \Eluceo\iCal\Domain\Entity\Calendar $calendar;

    /** @var string string */
    protected string $prefixName;

    public function __construct(string $fileName, Eluceo\iCal\Domain\Entity\Calendar $calendar)
    {
        $this->calendar = $calendar;
        $this->fileName = $fileName;
        $this->prefixName = Uuid::v4()->toRfc4122();
    }

    /**
     * @return mixed
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        $componentFactory = new \Eluceo\iCal\Presentation\Factory\CalendarFactory();

        return $componentFactory->createCalendar($this->calendar);
    }

    /**
     * @return string
     *
     * @throws Exception
     */
    public function getFile()
    {
        try {
            $file = tmpfile();
            fwrite($file, $this->getData());

            $filePath = _files_ . '/' . _folder_lms_ . '/calendar/' . $this->prefixName . '_' . str_replace(' ', '_', $this->getFileName());

            copy(stream_get_meta_data($file)['uri'], $filePath);

            return $filePath;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function getFileUrl()
    {
        $filePath = str_replace(_files_, _folder_files_, $this->getFile());

        return Get::site_url() . $filePath;
    }

    public function download()
    {
// 4. Set HTTP headers.
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $this->fileName . '"');
        echo $this->getData();
    }
}
