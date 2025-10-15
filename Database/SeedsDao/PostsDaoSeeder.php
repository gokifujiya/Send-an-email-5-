<?php
namespace Database\SeedsDao;

use Database\DataAccess\DAOFactory;
use Models\Post;

class PostsDaoSeeder
{
    public function seed(int $count = 50): void
    {
        $dao = DAOFactory::getPostDAO();

        for ($i = 1; $i <= $count; $i++) {
            $post = new Post(
                subject: "Subject $i",
                content: "This is sample post content #$i"
            );
            $dao->create($post);
        }
    }
}
