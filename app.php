use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;

// create the Silex application
$app = new Application();
$app->register(new TwigServiceProvider());
$app['twig.path'] = [ __DIR__ ];

$app->get('/', function () use ($app) {
    /** @var PDO $db */
    $db = $app['database'];
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    // Show existing guestbook entries.
    $results = $db->query('SELECT * from entries');

    return $twig->render('cloudsql.html.twig', [
        'results' => $results,
    ]);
});

$app->post('/', function (Request $request) use ($app) {
    /** @var PDO $db */
    $db = $app['database'];

    $name = $request->request->get('name');
    $content = $request->request->get('content');

    if ($name && $content) {
        $stmt = $db->prepare('INSERT INTO entries (guestName, content) VALUES (:name, :content)');
        $stmt->execute([
            ':name' => $name,
            ':content' => $content,
        ]);
    }

    return $app->redirect('/');
});

// function to return the PDO instance
$app['database'] = function () use ($app) {
    // Connect to CloudSQL from App Engine.
    $dsn = getenv('MYSQL_DSN');
    $user = getenv('MYSQL_USER');
    $password = getenv('MYSQL_PASSWORD');
    if (!isset($dsn, $user) || false === $password) {
        throw new Exception('Set MYSQL_DSN, MYSQL_USER, and MYSQL_PASSWORD environment variables');
    }

    $db = new PDO($dsn, $user, $password);

    return $db;
};
appengine/standard/cloudsql/cloudsql.html.twig VIEW ON GITHUB
<html>
    <body>
        {% if results %}
            <h2>Guestbook Entries</h2>

            {% for row in results %}
                <div><strong> {{ row.guestName }} </strong> wrote <br> {{ row.content }}</div>
            {% endfor %}
        {% endif %}

        <h2>Sign the Guestbook</h2>
        <form action="/" method="post">
            <div>Name: <input name="name" /></div>
            <div><textarea name="content" rows="3" cols="60"></textarea></div>
            <div><input type="submit" value="Sign Guestbook"></div>
        </form>
    </body>
</html>