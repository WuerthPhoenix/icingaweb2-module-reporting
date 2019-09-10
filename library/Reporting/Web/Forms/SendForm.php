<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Actions\SendMail;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedReports;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\DivDecorator;
use ipl\Html\Form;
use Icinga\Util\Translator;

class SendForm extends Form
{
    use Database;
    use ProvidedReports;

    /** @var Report */
    protected $report;

    public function setReport(Report $report)
    {
        $this->report = $report;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $types = ['pdf' => 'PDF'];

        if ($this->report->providesData()) {
            $types['csv'] = 'CSV';
            $types['json'] = 'JSON';
        }

        $this->addElement('select', 'type', [
            'required'  => true,
            'label'     => Translator::translate('Type'),
            'options'   => [null => Translator::translate('Please choose')] + $types
        ]);

        $this->addElement('textarea', 'recipients', [
            'required' => true,
            'label'    => Translator::translate('Recipients')
        ]);

        $this->addElement('submit', 'submit', [
            'label' => Translator::translate('Send Report')
        ]);
    }

    public function onSuccess()
    {
        $values = $this->getValues();

        $sendMail = new SendMail();

        $sendMail->execute($this->report, $values);
    }
}
