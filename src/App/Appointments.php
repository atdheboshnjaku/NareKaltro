<?php

declare(strict_types = 1);

namespace Fin\Narekaltro\App;

class Appointments extends Database
{

    private $table = "Appointments";
    private $table_2 = "Users";
    private $table_3 = "Services";
    private $table_4 = "Business_Locations";

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
                $service = $this->getService($appointment['service_id']);
                $location = $this->getLocation($appointment['location_id']);

                if($appointment['end_date'] != '') {

                    $appointmentList[] = [
                        'id'        => (int) $appointment[$columnName['COLUMN_NAME']],
                        'title'     => $user['name'],
                        'extendedProps' => [
                            'location' => $location['name'],
                            'service' => $service['name'] 
                        ],
                        'start'     => $appointment['start_date'],
                        'end'       => $appointment['end_date'],
                        //'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
                        'color' => $service['background'],
                        'textColor' => $service['color'] 
                        
                    ];
                } else {
                    $appointmentList[] = [
                        'id'        => (int) $appointment[$columnName['COLUMN_NAME']],
                        'title'     => $user['name'],
                        'extendedProps' => [
                            'location' => $location['name'],
                            'service' => $service['name'] 
                        ],
                        'start'     => $appointment['start_date'],
                        //'url'       => '/appointment/edit?id=' . (int) $appointment[$columnName['COLUMN_NAME']],
                        'color' => $service['background'],
                        'textColor' => $service['color'] 
                    ];
                }     

            endforeach;

            return json_encode($appointmentList);
        }

    }

    public function addAppointment(array $params = null): bool
    {

        if(!empty($params)) {

            $params['status'] = "1";
            $this->prepareToInsert($params);
            $out = $this->insert($this->table);
            $this->id = $this->id;
            return $out;

        }
        return false;
        

    }

    public function getUser(string $id): array
    {

        $sql = "SELECT `name` FROM {$this->table_2}
                WHERE `id` = '". $this->escape($id) . "'";
                return $this->fetchOne($sql);

    }

    public function getService(string $id): array 
    {

        $sql = "SELECT `name`, `background`, `color` FROM {$this->table_3}
                WHERE `id` = '". $this->escape($id) . "'";
                return $this->fetchOne($sql);

    }

    public function getLocation(string $id): array 
    {

        $sql = "SELECT `name` FROM {$this->table_4}
                WHERE `id` = '". $this->escape($id) . "'";
                return $this->fetchOne($sql);

    }

    public function updateAppointmentDate($appointment_id, $start_date, $end_date): bool 
    {


        $sql = "UPDATE {$this->table} 
                SET `start_date` = '". $this->escape($start_date) ."',
                `end_date` = '". $this->escape($end_date) ."'
                WHERE `appointment_id` = '". $this->escape($appointment_id) ."'";
                return $this->query($sql);


    }



}