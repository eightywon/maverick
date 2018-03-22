<?php
	//use PDO;
	class Database {
		private $_connection;
		private static $_instance;

		public static function getInstance() {
			if (!self::$_instance) {
				self::$_instance=new self();
			}
			return self::$_instance;
		}

		public function __construct() {
			$this->_connection=new PDO("sqlite:the.db");
		}

		private function __clone() {
		}

		public function getConnection() {
			return $this->_connection;
		}

		public function update($query,$pdo) {
			$result=$pdo->query($query);
			if ($result===false) {
				return 'fail';
			} else {
				return 'success';
			}
		}

		public function delete($query,$pdo) {
			$result=$pdo->query($query);
			if ($result===false) {
				return 'fail';
			} else {
				return 'success';
			}
		}

		public function select($query,$pdo) {
			$rows=array();
			$result=$pdo->query($query);
			if ($result===false) {
				return false;
			} else {
				while ($row=$result->fetch(PDO::FETCH_ASSOC)) {
					$rows[]=$row;
				}
				return $rows;
			}
		}

		public function selectSingle($query,$pdo) {
			$result=$pdo->query($query);

			if ($result===false) {
				return false;
			} else {
			        $single=$result->fetch(PDO::FETCH_ASSOC);
				//$row=$result->fetchColumn(0);
				//return $row;
				return $single;
			}

		}
	}
?>
