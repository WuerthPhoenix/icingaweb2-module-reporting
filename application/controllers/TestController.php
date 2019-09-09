<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Controllers;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Timeframe;
use Icinga\Module\Reporting\Web\Controller;
use ipl\Html\Table;
use ipl\Sql\Select;
use dipl\Translation\TranslationHelper;

class TestController extends Controller
{
    use Database;

    public function timeframesAction()
    {
        $select = (new Select())
            ->from('timeframe')
            ->columns('*');

        $table = new Table();

        $table->getAttributes()->add('class', 'common-table');

        $table->getHeader()->add(Table::row([$this->translate('Name', 'reporting'),
                                             $this->translate('Title', 'reporting'),
                                             $this->translate('Start', 'reporting'),
                                             $this->translate('End', 'reporting')],
                                             null, 'th'));

        foreach ($this->getDb()->select($select) as $row) {
            $timeframe = (new Timeframe())
                ->setName($row->name)
                ->setTitle($row->title)
                ->setStart($row->start)
                ->setEnd($row->end);

            $table->getBody()->add(Table::row([
                $timeframe->getName(),
                $timeframe->getTitle(),
                $timeframe->getTimerange()->getStart()->format('Y-m-d H:i:s'),
                $timeframe->getTimerange()->getEnd()->format('Y-m-d H:i:s')
            ]));
        }

        $this->setTitle($this->translate('Time Frames', 'reporting'));

        $this->addContent($table);
    }
}
