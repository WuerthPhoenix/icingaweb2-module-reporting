<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use GuzzleHttp\Psr7\ServerRequest;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\Controller;
use Icinga\Module\Reporting\Web\Forms\TimeframeForm;
use Icinga\Module\Reporting\Web\ReportsAndTimeframesTabs;
use ipl\Html\Html;
use ipl\Sql\Select;
use reportingipl\Web\Url;
use reportingipl\Web\Widget\ButtonLink;
use dipl\Translation\TranslationHelper;

class TimeframesController extends Controller
{
    use Database;
    use ReportsAndTimeframesTabs;

    public function indexAction()
    {
        $this->createTabs()->activate('timeframes');

        $new = new ButtonLink(
            $this->translate('New Time Frame'),
            Url::fromPath('reporting/timeframes/new')->getAbsoluteUrl('&'),
            'plus'
        );

        $this->addControl($new);

        $tableRows = [];

        $select = (new Select())
            ->from('timeframe t')
            ->columns('*');

        foreach ($this->getDb()->select($select) as $timeframe) {
            $url = Url::fromPath('reporting/timeframe/edit', ['id' => $timeframe->id])->getAbsoluteUrl('&');

            $tableRows[] = Html::tag('tr', ['href' => $url], [
                Html::tag('td', null, $timeframe->name),
                Html::tag('td', null, $timeframe->start),
                Html::tag('td', null, $timeframe->end),
                Html::tag('td', null, date('Y-m-d H:i', $timeframe->ctime / 1000)),
                Html::tag('td', null, date('Y-m-d H:i', $timeframe->mtime / 1000))
            ]);
        }

        if (! empty($tableRows)) {
            $table = Html::tag(
                'table',
                ['class' => 'common-table table-row-selectable', 'data-base-target' => '_next'],
                [
                    Html::tag(
                        'thead',
                        null,
                        Html::tag(
                            'tr',
                            null,
                            [
                                Html::tag('th', null, $this->translate('Name')),
                                Html::tag('th', null, $this->translate('Start')),
                                Html::tag('th', null, $this->translate('End')),
                                Html::tag('th', null, $this->translate('Date Created')),
                                Html::tag('th', null, $this->translate('Date Modified'))
                            ]
                        )
                    ),
                    Html::tag('tbody', null, $tableRows)
                ]
            );

            $this->addContent($table);
        } else {
            $this->addContent(Html::tag('p', null, $this->translate('No time frames created yet.')));
        }
    }

    public function newAction()
    {
        $this->setTitle($this->translate('New Time Frame'));

        $form = new TimeframeForm();
        $form->handleRequest(ServerRequest::fromGlobals());

        $this->redirectForm($form, 'reporting/timeframes');

        $this->addContent($form);
    }
}
