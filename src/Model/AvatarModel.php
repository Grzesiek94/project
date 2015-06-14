<?php
/**
 * Photos model.
 *
 * @author EPI <epi@uj.edu.pl>
 * @link http://epi.uj.edu.pl
 * @copyright 2015 EPI
 */

namespace Model;

use Silex\Application;

/**
 * Class PhotosModel.
 *
 * @category Epi
 * @package Model
 * @use Silex\Application
 */
class AvatarModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $db
     */
    protected $db;

    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }

    /**
     * Save image.
     *
     * @access public
     * @param array $image Image data from request
     * @param string $mediaPath Path to media folder on disk
     * @throws \PDOException
     * @return mixed Result
     */
    public function saveImage($image, $mediaPath, $id)
    {
        try {
            $originalFilename = $image['avatar']->getClientOriginalName();
            $newFilename = $this->createName($originalFilename, $id);
            $image['avatar']->move($mediaPath, $newFilename);
            $this->saveFilename($newFilename, $id);
            return true;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Save filename in database.
     *
     * @access protected
     * @param string $name Filename
     * @return mixed Result
     */
    protected function saveFilename($name, $id)
    {
        $data = array('id' => $id, 'avatar' => $name);
        return $this->db->update('users_data', $data, array('id' => $id));
    }

    /**
     * Creates random filename.
     *
     * @access protected
     * @param string $name Source filename
     *
     * @return string Result
     */
    protected function createName($name, $id)
    {
        $newName = '';
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = $id . '.' . $ext;
        return $newName;
    }
}
