<?php

namespace Restfull\Mail;

use App\Model\AppModel;
use Restfull\Error\Exceptions;
use PHPMailer\PHPMailer\Exception;

/**
 *
 */
class PrepareService
{

    /**
     * @var Email
     */
    private $mail;

    /**
     * @var object
     */
    private $ORM;

    /**
     * @var string
     */
    private $table = 'services';

    /**
     * @param Email $mail
     * @param object $ORM
     * @param string $table
     * @return $this
     */
    public function startORM(Email $mail, object $ORM, string $table = 'services'): PrepareEmail
    {
        if ($ORM instanceof AppModel) {
            throw new Exceptions('This variavel not acceptable object AppModel.', 404);
        }
        $this->mail = $mail;
        $this->ORM = $ORM;
        if ($this->table != $table) {
            $this->table = $table;
        }
        return $this;
    }

    /**
     * @param array $datas
     * @return PrepareEmail
     */
    public function writeData(array $datas): PrepareEmail
    {
        foreach (['to' => 'recipients', 'bcc' => 'recipientsBcc', 'cc' => 'recipientsCc'] as $partMethod => $key) {
            if (!isset($datas[$key])) {
                if ($this->mail->validerAddress($datas[$key])) {
                    $method = 'get' . ucfirst($partMethod) . 'Addresses';
                    $$key = $this->mail->{$method}();
                    if (is_array($$key)) {
                        $$key = count($$key) > 1 ? implode(';', $$key) : $$key;
                    }
                    $datas[$key] = $$key;
                }
            }
        }
        $options = ['fields' => array_keys($datas), 'conditions' => $datas];
        $orm = $this->ORM->tableRepository(['main' => [['table' => $this->table]]],
            ['datas' => $options]);
        $orm->typeQuery('create')->queryAssembly()->excuteQuery();
        return $this;
    }

    /**
     * @param array $send
     * @return bool
     * @throws Exceptions
     * @throws Exception
     */
    public function readData(array $send): bool
    {
        $options[0]['fields'] = [
            'recipients',
            'if(recipientsBcc is null,"",recipientsBcc) as bcc',
            'if(recipientsCc is null,"",recipientsCc) as cc',
            'subject',
            'message'
        ];
        $options[0]['conditions'] = ['status & ' => 'Ativo'];
        $orm = $this->ORM->tableRepository(['main' => [['table' => $this->table]]],
            ['datas' => $options]);
        $result = $orm->typeQuery('all')->queryAssembly()->executeQuery();
        if (stripos($result['recipients'], ';') !== false) {
            $result['recipients'] = explod(';', $result['recipients']);
        }
        $this->addressing($send, $result['recipients']);
        foreach (['bcc', 'cc'] as $key) {
            if ($this->mail->validerAddress($result[$key])) {
                $method = $key == 'bcc' ? 'hiddenCopy' : 'copy';
                $this->{$method}($result[$key]);
            }
        }
        return $this->sends($result['subject'], $result['message']);
    }

    /**
     * @param int $id
     * @param string $data
     * @return $this
     */
    public function changeStatus(int $id, string $data): PrepareService
    {
        $options[0]['fields'] = [
            'recipients',
            'if(recipientsBcc is null,"",recipientsBcc) as recipientsBcc',
            'if(recipientsCc is null,"",recipientsCc) as recipientsCc',
            'status'
        ];
        $options[0]['conditions'] = ['status & ' => 'Ativo', 'id & ' => $id];
        $orm = $this->ORM->tableRepository(['main' => [['table' => $this->table]]],
            ['datas' => $options]);
        $result = $orm->typeQuery('all')->queryAssembly()->executeQuery();
        $found = ['notFoundSearch', 'notFoundSearch'];
        foreach (['recipientsBcc', 'recipientsCc'] as $number => $key) {
            if ($this->mail->validerAddress($result[$key])) {
                if (stripos($result[$key], ';') !== false) {
                    $result[$key] = explode(';', $result[$key]);
                    unset($result[$key][array_keys($data, $result[$key])]);
                    $result[$key] = count($result[$key]) > 1 ? implode(';', $result[$key]) : (count(
                        $result[$key]
                    ) > 0 ? $result[$key][0] : '');
                    $found[$number] = 'foundSearch';
                }
            } else {
                unser($result[$key]);
            }
        }
        if (in_array('foundSearch', $found) === false) {
            if (stripos($result['recipients'], ';') !== false) {
                $result['recipients'] = explode(';', $result['recipients']);
                if (count($result['recipients']) > 0) {
                    unset($result['recipients'][array_keys($data, $result['recipients'])]);
                    $result['recipients'] = implode(';', $result['recipients']);
                    unset($result['status']);
                } else {
                    $result['status'] = 'desativado';
                }
            }
        } else {
            unset($result['recipients']);
        }
        $options[0]['fields'] = $result;
        unset($options[0]['conditions']['status & ']);
        $orm->typeQuery('update')->queryAssembly()->executeQuery();
        return $this;
    }

}