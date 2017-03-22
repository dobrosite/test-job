<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__.'/../app/app.db',
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../app/views',
));

$app->get(
    '',
    function () use ($app) {
        $images = $app['db']->fetchAll('SELECT * FROM images ORDER BY sort');
        return $app['twig']->render('index.html.twig', ['images' => $images]);
    }
);

$app->post(
    'upload.php',
    function () use ($app) {
        $count = $app['db']->fetchColumn("SELECT COUNT(image) FROM images");
        move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/uploads/' . $_FILES['file']['name']);
        $app['db']->executeQuery("INSERT INTO images (image, sort) VALUES (?, ?)",
            [$_FILES['file']['name'], $count+1]);
        return new \Symfony\Component\HttpFoundation\Response('', 301, ['Location' => $_SERVER['HTTP_REFERER']]);
    }
);

$app->get(
    'move.php',
    function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
        /** @var \Doctrine\DBAL\Connection $db */
        $db = $app['db'];

        $img = $app['db']->fetchAssoc('SELECT * FROM images WHERE sort = ?',
            [$request->query->get('n')]);
        if ($request->query->get('t') == -1) {
            $swp = $app['db']->fetchAssoc('SELECT * FROM images WHERE sort < ? ORDER BY sort DESC',
                [$img['sort']]);
            $swp['sort']++;
            $img['sort']--;
        } elseif ($request->query->get('t') == 1) {
            $swp = $app['db']->fetchAssoc('SELECT * FROM images WHERE sort > ? ORDER BY sort ASC',
                [$img['sort']]);
            $swp['sort']--;
            $img['sort']++;
        }
        $db->executeUpdate('UPDATE images SET sort = ? WHERE image = ?',
            [$img['sort'], $img['image']]);
        $db->executeUpdate('UPDATE images SET sort = ? WHERE image = ?',
            [$swp['sort'], $swp['image']]);

        return 'OK';
    }
);

$app->run();
