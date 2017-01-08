<?php

namespace AmauryCarrade\Controllers\Tools;

use Requests;
use Requests_Hooks;
use Requests_Response;
use Requests_Session;
use RuntimeException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class TeaController
{
    /**
     * MariageFrères' base URL, used for relative links.
     */
    const MF_BASE_URL = 'http://www.mariagefreres.com/';

    /**
     * MariageFrères' base french URL, used for relative links.
     */
    const MF_BASE_URL_FR = 'http://www.mariagefreres.com/FR/';

    /**
     * MariageFrères' homepage URL.
     */
    const MF_HOMEPAGE = 'http://www.mariagefreres.com/FR/accueil.html';

    /**
     * MariageFrères' no-results page URL.
     */
    const MF_NO_RESULTS = 'http://www.mariagefreres.com/FR/plus_de_thes.html';

    /**
     * MariageFrères' teas pages URLs.
     * These URLs will be tested until one does not leads to a 404 or a redirect to the homepage.
     * {0} will be replaced with a slug-like placeholder, and {1} with the tea ID.
     */
    const MF_RESULTS = array(
        'http://www.mariagefreres.com/FR/2-{0}-TC{1}.html',
        'http://www.mariagefreres.com/FR/2-{0}-TB{1}.html',
        'http://www.mariagefreres.com/FR/2-{0}-TE{1}.html',
        'http://www.mariagefreres.com/FR/2-{0}-T{1}.html'
    );

    /**
     * For these search, directly redirect to the given tea (ID).
     */
    const SPECIAL_SEARCHES = array(
        'noël' => 9215, 'noel' => 9215,  // Black Christmas Tea http://www.mariagefreres.com/FR/2-noel-tea-the-noir-gourmand-TE9215.html
        'opera' => 952, 'opéra' => 952,  // The first result is either red opera or a box with 4 teas, containing none of the required infos.
        'thé à l\'opéra' => 952,         // Same, with various spelling errors too.
        'thé a l\'opéra' => 952,
        'thé a l\'opera' => 952,
        'thé à l\'opera' => 952,
    );


    public function homepage(Application $app, Request $request)
    {
        if ($request->query->has('search'))
        {
            $search = trim($request->query->get('search'));
            if ($search)
                return $app->redirect($app['url_generator']->generate('tools.tea.results', array('search' => $search)));
            else
                return $app->redirect($app['url_generator']->generate('tools.tea'));
        }

        return $app['twig']->render('tools/tea/tea.html.twig', array(
            'tea' => array('success' => false, 'error' => null),
            'input' => ''
        ));
    }

    public function search(Application $app, $search, $format)
    {
        $format = strtolower($format);

        try
        {
            $tea = array_merge(array('success' => true, 'error' => null), self::load_tea($search));
        }
        catch (RuntimeException $e)
        {
            $tea = array("success" => false, "error" => $e->getMessage());
        }

        if ($format == 'json')
        {
            return $app->json($tea);
        }
        else
        {
            return $app['twig']->render('tools/tea/tea.html.twig', array(
                'tea' => $tea,
                'input' => $search
            ));
        }
    }


    /**
     * Retrieves infos from a tea name or ID
     *
     * @param string|int $tea the tea name or ID
     * @return array|null retrieved infos
     *      [
     *          'name', 'description', 'long_description', 'url',
     *          'tips': [
     *              'raw', 'mass' (float, g), 'volume' (float, cl), 'temperature' (float, °C), 'duration' (float, min)
     *          ]
     *      ]
     */
    private function load_tea($tea)
    {
        return is_numeric($tea) ? self::load_tea_from_id((int) $tea) : self::load_tea_from_name($tea);
    }

    /**
     * Loads infos from a tea ID
     *
     * @param int $tea_id The tea ID
     * @return array|null retrieved infos
     */
    private function load_tea_from_id($tea_id)
    {
        if ($tea_id == null) return null;

        $tea_profile_page = null;
        $tea_url = null;

        foreach (self::MF_RESULTS as $url)
        {
            $r = self::load_mariage_url(strtr($url, array('{0}' => 'pomf-slug', '{1}' => $tea_id)));
            if ($r == null) continue;

            $tea_profile_page = $r->body;
            $tea_url = $r->url;
            break;
        }

        if ($tea_profile_page == null)
            throw new RuntimeException("Impossible de charger la page du thé");

        return self::retrieve_tea_data_from_document($tea_profile_page, $tea_url);
    }

    /**
     * Loads infos from a tea name, by performing a search and looking for the first result.
     *
     * @param string $tea_name The tea name.
     * @return array|null retrieved infos
     */
    private function load_tea_from_name($tea_name)
    {
        // First we check if we are in a case of a special search.
        $lower_tea_name = strtolower($tea_name);
        if (array_key_exists($lower_tea_name, self::SPECIAL_SEARCHES))
        {
            return self::load_tea_from_id(self::SPECIAL_SEARCHES[$lower_tea_name]);
        }

        $s = self::create_session();

        // Loads the homepage before to act like a visitor and load cookies
        $s->get(self::MF_HOMEPAGE);

        // Then we use the search form; it makes a POST request against the home page
        $r = $s->post(self::MF_HOMEPAGE, array(), array(
            'WD_BUTTON_CLICK_' => 'M8',
            'WD_ACTION_'       => '',
            'M3'               => $tea_name,
            'M12'              => ''
        ));

        if ($r->status_code >= 300 || strtolower($r->url) == strtolower(self::MF_NO_RESULTS))
            throw new RuntimeException("Impossible de charger la page du thé : pas de résultats ou erreur HTTP rencontrée.");

        // Now we extract the first search result
        $soup = str_get_html($r->body);
        if ($soup === false)
            throw new RuntimeException("L'analyse de la page a échouée.");

        $result_links = $soup->find('.Lien-Titre-Liste a');
        $result_link = null;

        foreach ($result_links as $link)
        {
            // Links formats:
            // → ./2-theiere-iskandar-en-argent-925-A3021.html
            // → ./2-iskandar-T984.html
            // → ./2-iskandar-boite-classique-100g-TC984.html
            // We want links to a tea, i.e. the ID at the end of the URL must start with a T.
            $link_id = array_pop(explode('-', explode('.', str_replace('/', '', str_replace('./', '', $link->href)))[0]));

            if (!empty($link_id) && strtoupper($link_id[0]) == 'T')
            {
                $result_link = $link;
                break;
            }
        }

        if ($result_link == null)
            throw new RuntimeException('Aucun des résultats de la recherche n\'est un thé.');

        $soup->clear();
        unset($soup);

        $result_link = $result_link->href;

        if (substr($result_link, 0, 2) == './')
        {
            $result_link = self::MF_BASE_URL_FR . substr($result_link, 2);
        }
        else if (substr($result_link, 0, 1) == '/')
        {
            $result_link = self::MF_BASE_URL . substr($result_link, 1);
        }

        $r = self::load_mariage_url($result_link, $s);
        if ($r == null) throw new RuntimeException('Impossible de charger la page du thé (via la recherche) ; page tentée : ' . $result_link . '.');

        return self::retrieve_tea_data_from_document($r->body, $result_link);
    }

    /**
     * Extracts tea infos from a tea profile page.
     *
     * @param string $tea_html_document The HTML source of the tea profile page.
     * @param string|null $origin_url The profile page of the tea being analyzed.
     * @return array|null retrieved infos
     */
    private function retrieve_tea_data_from_document($tea_html_document, $origin_url = null)
    {
        $soup = str_get_html($tea_html_document);

        $name = str_replace('®', '', $soup->find('h1')[0]->innertext);
        $description = str_replace(array("\r\n", '  ', '<br />', '<br/>'), ' ', $soup->find('h2')[0]->innertext);
        $long_description = str_replace("\r", '', $soup->find('#fiche_desc')[0]->innertext);

        $tips_tags = $soup->find('#fiche_conseil_prepa')[0];
        $tips = trim(str_ireplace('conseils de préparation :', '', str_replace('CONSEILS DE PRÉPARATION :', '', $tips_tags->plaintext)));

        // We try to extract raw data.
        // Usual format: "2,5 g / 20 cl - 95°C - 5 min"

        // Conversion of '/' to '-' to cut the string, and ',' to '.' to parse float numbers.
        $tips_parts = explode(' - ', str_replace(array('/', ','), array('-', '.'), $tips));

        $tips_mass = null;
        $tips_volume = null;
        $tips_temperature = null;
        $tips_duration = null;

        foreach ($tips_parts as $tip)
        {
            $tip_int = floatval($tip);

            // Mass
            if (strpos($tip, 'g') !== false)
            {
                $tips_mass = $tip_int;
            }
            else if (strpos($tip, 'cl') !== false)
            {
                $tips_volume = $tip_int;
            }
            else if (strpos($tip, '°C') !== false)
            {
                $tips_temperature = $tip_int;
            }
            else if (strpos($tip, 'min') !== false)
            {
                $tips_duration = $tip_int;
            }
        }

        $soup->clear();
        unset($soup);

        return array(
            'name' => $name,
            'description' => $description,
            'long_description' => $long_description,
            'url' => $origin_url,
            'tips' => array(
                'raw' => $tips,
                'mass' => $tips_mass,
                'volume' => $tips_volume,
                'temperature' => $tips_temperature,
                'duration' => $tips_duration
            )
        );
    }

    /**
     * Loads a MariageFrères URL. Handles the 404-as-redirect-to-home behavior.
     *
     * @param string                $url      The URL to load.
     * @param Requests_Session|null $session  A session to use, if any.
     *
     * @return Requests_Response|null a response, or null if not found.
     */
    private function load_mariage_url($url, Requests_Session $session = null)
    {
        $r = $session != null ? $session->get($url) : Requests::get($url);

        if ($r->status_code >= 300 || strpos($r->url, 'accueil.html') !== false)
        {
            return null;
        }

        return $r;
    }

    /**
     * Creates and returns a new Requests_Session with all options needed.
     * @return Requests_Session a new session.
     */
    private function create_session()
    {
        // Browser-like 302 handling (switch to GET)
        $hooks = new Requests_Hooks();
        $hooks->register('requests.before_redirect', function ($location, $headers, $data, &$options, $original)
        {
            if ($original->status_code === 301 || $original->status_code === 302)
            {
                $options['type'] = Requests::GET;
            }
        });

        $s = new Requests_Session();
        $s->hooks = $hooks;
        $s->headers['User-Agent'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0';

        return $s;
    }

    private function extract_int($s)
    {
        return ($a = preg_replace('/[^\-\d]*(\-?\d*).*/', '$1', $s)) ? (int) $a : 0;
    }
}
