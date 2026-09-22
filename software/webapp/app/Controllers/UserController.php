<?php

    namespace Controllers;

    use Models\users;
    use Models\puntos;

    class UserController {

        public function __construct(){
            
        }

        public function getUsers(){
            $user = new users();
            $result = $user->select(['id', 'username','name', 'email',])->get();

            if(count(json_decode($result)) > 0){
                //Se registra la sesión
                return $result;
                 
            }else{
                echo json_encode(["r" => false ]);
            }
        }

        public function getUserPoints() {
            $users = new users(); // Modelo para manejar la tabla "puntos"    a=puntos b=usuarios c=dispositivos
            $result = $users->select(['a.id', 'a.name', 'a.email', 'GROUP_CONCAT(b.name SEPARATOR ", ") AS puntos_asignados'])
                             ->LEFTjoin('puntos b', 'a.id = b.id_user')
                             ->groupBy(['a.id'])
                             ->get();
                return $result;
        }

        public function setUserActive($uid){
            $user = new users();
            $result = $user->where([['id', $uid]])->update();
        }

        
}

