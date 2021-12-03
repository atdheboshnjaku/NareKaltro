<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Appointments extends Database
{

    private $table = "Appointments";
    private $table_2 = "Users";

    public function getColumnName(): array  
    {

        return $this->getTableColumnName($this->table);

    }

    public function getAppointmentsJSON(): ?string 
    {

        $columnName = $this->getColumnName();
        $appointmentList = [];
        $sql = "SELECT * FROM {$this->table}";
        $appointments = $this->fetchAll($sql);
        if($appointments) {
            foreach($appointments as $appointment):
                $user = $this->getUser($appointment['client_id']);

                if($appointment['end_date'] != '') {
                    $appointmentList[] = [
                        'id'        => (int) $appointment[$columnName['COLUMN_NAME']],
                        'title'     => $user['name'],
                        'start'     => $appointment['start_date'],
                        'end'       => $appointment['end_date'],
                        //'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
                        'color' => 'black',
                        'textColor' => '#fff' 
                    ];
                } else {
                    $appointmentList[] = [
                        'id'        => (int) $appointment[$columnName['COLUMN_NAME']],
                        'title'     => $user['name'],
                        'start'     => $appointment['start_date'],
                        //'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
                        'color' => 'black',
                        'textColor' => '#fff' 
                    ];
                }     

            endforeach;

            return json_encode($appointmentList);
        }

    }

    public function getUser(string $id): array
    {

        $sql = "SELECT `name` FROM {$this->table_2}
                WHERE `id` = '". $this->escape($id) ."'";
                return $this->fetchOne($sql);

    }



}