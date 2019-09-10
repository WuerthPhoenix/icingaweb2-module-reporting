<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web;

use Icinga\Util\Translator;

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
                'title'     => Translator::translate('Show Reports'),
                'label'     => Translator::translate('Reports'),
                'url'       => 'reporting/reports'
        ]);

        $tabs->add('timeframes', [
            'title'     => Translator::translate('Show Time Frames'),
            'label'     => Translator::translate('Time Frames'),
            'url'       => 'reporting/timeframes'
        ]);

        return $tabs;
    }
}
