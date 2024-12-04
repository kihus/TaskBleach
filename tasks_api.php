<?php

class Tasks{

    private $id = 0;
    private $taskName = null;
    private $taskDescription = null;
    private $taskStatus = null;

    // Setters and Getters
    public function setId(int $id) :void {$this->id = $id;}
      
    public function getId() :int {return $this->id;}
    
    public function setTaskName(string $taskName) :void {$this->taskName = $taskName;}
   
    public function getTaskName() :string {return $this->taskName;}

    public function setTaskDescription(string $taskDescription) :void {$this->taskDescription = $taskDescription;}

    public function getTaskDescription() :string {return $this->taskDescription;}

    public function setTaskStatus(string $taskStatus) :void {$this->taskStatus = $taskStatus;}

    public function getTaskStatus() :string {return $this->taskStatus;}

 


    // Connection with the database
    private function connection() : \PDO {return new \PDO 
        ("mysql:host=localhost;dbname=db_task", "root", "");
    }

    // Create
    public function create() :array
    {
        $con = $this->connection();
        $stmt = $con->prepare("INSERT INTO tasks (taskName, taskDescription, taskStatus) VALUES (:_taskName, :_taskDescription, :_taskStatus)");
        $stmt->bindValue(":_taskName", $this->getTaskName(), \PDO::PARAM_STR);  
        $stmt->bindValue(":_taskDescription", $this->getTaskDescription(), \PDO::PARAM_STR);
        $stmt->bindValue(":_taskStatus", $this->getTaskStatus(), \PDO::PARAM_STR);
       
        if($stmt->execute()){
            $this->setId($con->lastInsertId());
            return $this->read();
        }
        return[];
    }

    // Read
    public function read() :array
    {
        $con = $this->connection();
            if($this->getId() === 0){
                $stmt = $con->prepare("SELECT * FROM tasks");
                if($stmt->execute()){
                    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
            }else if($this->getId() > 0){
                $stmt = $con->prepare("SELECT * FROM tasks WHERE id = :_id");
                $stmt->bindValue(":_id", $this->getId(), \PDO::PARAM_INT);
                if($stmt->execute()){
                    return $stmt->fetchAll(\PDO::FETCH_ASSOC );
                }       
            }
        return [];
    }

    public function update() :array
    {
        $con = $this->connection();
        $stmt = $con->prepare("UPDATE tasks SET taskName = :_taskName, taskDescription = :_taskDescription, taskStatus = :_taskStatus WHERE id = :_id");
        $stmt->bindValue(":_taskName", $this->getTaskName(), \PDO::PARAM_STR);
        $stmt->bindValue(":_taskDescription", $this->getTaskDescription(), \PDO::PARAM_STR);
        $stmt->bindValue(":_id", $this->getId(), \PDO::PARAM_INT);
        $stmt->bindValue(":_taskStatus", $this->getTaskStatus(), \PDO::PARAM_STR);
        if($stmt->execute()){
            return $this->read();
        }
        return [];

    }

    public function delete() :array{
        $task = $this->read();
        $con = $this->connection();
        $stmt = $con->prepare("DELETE FROM tasks WHERE id = :_id");

        $stmt->bindValue(":_id", $this->getId(), \PDO::PARAM_INT);
        if($stmt->execute()){
            return $task;
            
        }
        return [];
    }
}
