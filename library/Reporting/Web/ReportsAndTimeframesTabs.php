<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use dipl\Translation\TranslationHelper;

trait ReportsAndTimeframesTabs
{
    /**
     * Create tabs
     *
     * @return  \Icinga\Web\Widget\Tabs
     */
    protected function createTabs()
    {
        $tabs = $this->getTabs();

        $tabs->add('reports', [
                'title'     => $this->translate('Show Reports', 'reporting'),
                'label'     => $this->translate('Reports', 'reporting'),
                'url'       => 'reporting/reports'
        ]);

        $tabs->add('timeframes', [
            'title'     => $this->translate('Show Time Frames', 'reporting'),
            'label'     => $this->translate('Time Frames', 'reporting'),
            'url'       => 'reporting/timeframes'
        ]);

        return $tabs;
    }
}
