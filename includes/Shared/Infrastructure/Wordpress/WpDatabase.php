<?php

namespace Contexis\Events\Shared\Infrastructure\Wordpress;

use Contexis\Events\Shared\Infrastructure\Contracts\Database;
use Contexis\Events\Shared\Infrastructure\Enums\DatabaseOutput;

class WpDatabase implements Database
{
    private \wpdb $db;

    public function __construct() 
    {
        global $wpdb;
        $this->db = $wpdb;
    }

	public function query(string $query): int|bool
	{		
		return $this->db->query($query);
	}

    public function getResults(string $query, DatabaseOutput $output = DatabaseOutput::OBJECT): array 
    {
        return $this->db->get_results($query, $output->value);
    }
	/*
	*  @param string      $query   Query statement with placeholders. Use %s for strings, %d for integers, and %f for floats. Example: $db->prepare("SELECT * FROM table WHERE id = %d", $id);
	 * @param mixed       ...$args Variables to substitute into the query's placeholders
	 * @return string|void Sanitized query string, if there is a query to prepare.
	 */
    public function prepare(string $query, ...$args): string 
    {
        return $this->db->prepare($query, ...$args);
    }

    public function getPrefix(): string 
    {
        return $this->db->prefix;
    }

	public function getVar(string $query, int $x = 0, int $y = 0): string|null
	{
		return $this->db->get_var($query, $x, $y);
	}

	public function getInt(string $query, int $x = 0, int $y = 0): int
	{
		return (int)$this->db->get_var($query, $x, $y);
	}

	public function getRow(string|null $query, DatabaseOutput $output = DatabaseOutput::OBJECT, int $y = 0): array|object|null
	{
		return $this->db->get_row($query, $output->value, $y);
	}

	public function getCol(string|null $query, int $x = 0): array
	{
		return $this->db->get_col($query, $x);
	}

	public function insert(string $table, array $data, array $format = []): int|false
	{
		$result = $this->db->insert($table, $data, $format);

		if ($result === false) {
			return false;
		}
		return $this->db->insert_id > 0 ? (int)$this->db->insert_id : $result;

	}

	public function replace(string $table, array $data, array $format = []): int|false
	{
		$result = $this->db->replace($table, $data, $format);
		return $result === false ? false : (int)$this->db->insert_id;
	}

	public function update(string $table, array $data, array $where, array $format = [], array $whereFormat = []): int|false
	{
		return $this->db->update($table, $data, $where, $format, $whereFormat);
	}

	public function delete(string $table, array $where, array $whereFormat = []): int|false
	{
		return $this->db->delete($table, $where, $whereFormat);
	}
	
}