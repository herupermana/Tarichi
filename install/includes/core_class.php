<?php

class Core
{
    // Function to validate the post data
    public function validate_post($data)
    {
        // Counter variable
        $counter = 0;

        // Validate the hostname
        if (isset($data['hostname']) and !empty($data['hostname'])) {
            $counter++;
        }
        // Validate the username
        if (isset($data['username']) and !empty($data['username'])) {
            $counter++;
        }
        // Validate the password
        if (isset($data['password']) and !empty($data['password'])) {
            // pass
        }
        // Validate the database
        if (isset($data['database']) and !empty($data['database'])) {
            $counter++;
        }

        // Check if all the required fields have been entered
        if ($counter == '3') {
            return true;
        } else {
            return false;
        }
    }

    public function validate_config($data)
    {
        $counter = 0;
        if (isset($data['username']) and !empty($data['username'])) {
            $counter++;
        }
        if (isset($data['password']) and !empty($data['password'])) {
            $counter++;
        }
        if (isset($data['site_name']) and !empty($data['site_name'])) {
            $counter++;
        }
        if ($counter == '3') {
            return true;
        } else {
            return false;
        }
    }

    // Function to show an error
    public function show_message($type, $message)
    {
        return $message;
    }

    // Function to write the config file
    public function write_config($data)
    {

        // Config path
        $template_path = 'config/database.php';
        $output_path = '../application/config/database.php';

        // Open the file
        $database_file = file_get_contents($template_path);

        $new = str_replace('%HOSTNAME%', $data['hostname'], $database_file);
        $new = str_replace('%USERNAME%', $data['username'], $new);
        $new = str_replace('%PASSWORD%', $data['password'], $new);
        $new = str_replace('%DATABASE%', $data['database'], $new);

        // Write the new database.php file
        $handle = fopen($output_path, 'w+');

        // Chmod the file, in case the user forgot
        @chmod($output_path, 0777);

        // Verify file permissions
        if (is_writable($output_path)) {

            // Write the file
            if (fwrite($handle, $new)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>

