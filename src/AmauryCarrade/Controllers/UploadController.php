<?php

namespace AmauryCarrade\Controllers;

use Downloader;
use Silex\Application;


class UploadController
{
    private static $folder_upload = 'files';
    private static $public_upload_root = 'https://raw.carrade.eu/files/';

    public function upload_form(Application $app)
    {
        return $app['twig']->render('upload.html.twig', array(
            'uploaded' => false
        ));
    }

    public function process_upload(Application $app)
    {
        $upload_dir_path = $app['web_folder'] . '/' . self::$folder_upload;

        if (!is_dir($upload_dir_path))
        {
            mkdir($upload_dir_path,0705);
            chmod($upload_dir_path, 0705);

            $h = fopen($upload_dir_path . '/.htaccess', 'w') or die("Can't create subdir/.htaccess file.");
            fwrite($h,"Options -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .htm .shtml .sh .cgi");
            fclose($h);

            $h = fopen($upload_dir_path . '/index.html', 'w') or die("Can't create subdir/index.html file.");
            fwrite($h,'<html><head><meta http-equiv="refresh" content="0;url='.$_SERVER["SCRIPT_NAME"].'"></head><body></body></html>');
            fclose($h);
        }

        $file_url = null;

        $success = false;
        $error_title = null;
        $error_text = null;

        $downloader = null;
        if (!empty($_POST['url_file']))
            $downloader = new Downloader();


        $filename = null;
        if (isset($_FILES['uploaded_file']) && $_FILES['uploaded_file']['error'] != UPLOAD_ERR_NO_FILE)
            $filename = $_FILES['uploaded_file']['name'];

        else
            $filename = basename($_POST['url_file']);
        
        $filename = $upload_dir_path . '/' . $filename;

        if (!self::check_key($_POST['password'], $app['credentials']['upload']))
        {
            $success = false;
            $error_title = 'Le mot de passe est incorrect.';

            if (strtolower($_POST['password']) != 'incorrect')
                $error_text  = 'Et ne pensez même pas à entrer « incorrect » pour voir.';
            else
                $error_text = 'J\'ai cru dire qu\'il était inutile d\'essayer « incorrect » ?...';
        }

        else if ($_FILES['uploaded_file']['error'] == UPLOAD_ERR_NO_FILE && empty($_POST['url_file']))
        {
            $success = false;
            $error_title = 'Aucun fichier.';
            $error_text  = 'Ce service ne permet pas (encore ?) de générer le fichier à téléverser au vol en lisant les pensées.';
        }

        else if ($_FILES['uploaded_file']['error'] == UPLOAD_ERR_FORM_SIZE)
        {
            $success = false;
            $error_title = 'Trop gros, passera pas.';
            $error_text  = 'Le fichier est trop massif. Réduisez-le.';
        }

        else if (!empty($_POST['url_file']) && $file_content = $downloader->get($_POST['url_file'], array(), 'curl'))
        {
            $file_content_data = $file_content['body'];
            $filename = self::determine_filename($upload_dir_path . '/' . basename($file_content['infos']['url']), $file_content_data);

            if (is_array($filename))
            {
                $file_url = self::get_file_url($filename[0]);
                $success = true;
            }
            
            else if ($file_content['HTTPCode'] != 200)
            {
                $success = false;
                $error_title = 'Téléchargement échoué !';
                $error_text  = 'Impossible de télécharger le fichier. Code HTTP obtenu : ' . $file_content['HTTPCode'];
            }

            else if (!$file = fopen($upload_dir_path . '/' . basename($filename), 'w'))
            {
                $success = false;
                $error_title = 'Impossible de créer le fichier.';
                $error_text  = 'Le fichier a bien été téléchargé, mais il n\'a pas été possible de le stocker localement.';
            }

            else if (!fwrite($file, $file_content_data))
            {
                $success = false;
                $error_title = 'Impossible d\'écrire le fichier.';
                $error_text  = 'Le fichier a bien été téléchargé, mais il n\'a pas été possible de le stocker localement.';
            }

            else
            {
                fclose($file);

                $success = true;
                $file_url = self::get_file_url($filename);
            }
        }

        else if (is_array($determined_filename = self::determine_filename($filename, $_FILES['uploaded_file']['tmp_name'], true)))
        {
            $success = true;
            $file_url = self::get_file_url($determined_filename[0]);
        }

        else if (move_uploaded_file($_FILES['uploaded_file']['tmp_name'], $filename))
        {
            $success = true;
            $file_url = self::get_file_url($filename);
        }

        else
        {
            $success = false;
            $error_title = 'Erreur...';
            $error_text  = 'Quelque chose s\'est mal passé. Mais alors quoi...';
        }

        return $app['twig']->render('upload.html.twig', array(
            'uploaded'       => true,
            'upload_success' => $success,
            'upload_url'     => $file_url,
            'error_title'    => $error_title,
            'error_text'     => $error_text
        ));
    }

    private function check_key($key, $hashed_valid_keys)
    {
        foreach ($hashed_valid_keys as $hashed_valid_key)
            if (hash('sha256', $key) == $hashed_valid_key)
                return true;

        return false;
    }

    /**
     * Determines filename.
     * @param  string       $filename    The input filename
     * @param  string       $newFileData The content of the new file (used to compare  files' contents)
     * @param  bool         $secondIsURL Is the second parameter an URL to the file?
     * @return string|array              The new filename, or an array who contains the filename if this file has already been uploaded.
     */
    private function determine_filename($filename, $newFileData, $secondIsURL = false) {
        $beforeFilename = str_replace(basename($filename), null, $filename);

        if (!file_exists($filename))
        {
            return $filename;
        }
        else
        {
            if ((!$secondIsURL && sha1_file($filename) == sha1($newFileData)) || ($secondIsURL && sha1_file($filename) == sha1_file($newFileData)))
            {
                return array($filename);
            }
            
            else
            {
                $i = 1;
                
                do
                {
                    $newFilename = $beforeFilename . $i . '-' . basename($filename);
                    $i++;
                } while(file_exists($newFilename) && ((!$secondIsURL && sha1_file($filename) != sha1($newFileData)) || ($secondIsURL && sha1_file($filename) != sha1_file($newFileData))));

                $i = $i - 2;
                $ext = $i <= 0 ? null : $i . '-';
                
                if (sha1_file($beforeFilename . $ext . basename($filename)) == sha1($newFileData))
                    return array($beforeFilename . $ext . basename($filename));
                
                return basename($newFilename);
            }
        }
    }

    /**
     * Return the complete file's URL from the file's path.
     *
     * @param $filename
     *
     * @return string
     */
    private function get_file_url($filename)
    {
        return self::$public_upload_root . basename($filename);
    }
}
