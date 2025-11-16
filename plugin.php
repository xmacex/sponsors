<?php

// Copying the functionaliry of the `Links` plugin.

class pluginSponsors extends Plugin
{
    public function init()
    {
        $jsondb = json_encode(array(
            'RUSTlab' => array(
                'prefix'  => 'The',
                'name'    => 'RUSTlab',
                'url'     => 'https://rustlab.rub.de',
                'suffix'  => 'at RUB',
                'logourl' => 'https://rustlab.ruhr-uni-bochum.de/wp-content/uploads/2023/02/logo-tentacle-500x500-1-1-200x200.png'
            )
        ));
        $this->dbFields = array(
            'jsondb' => $jsondb
        );

        // Disable default Save and Cancel button.
        $this->formButtons = false;
    }

    // Method called when a POST request is sent
    public function post()
    {
        // Get current jsondb value from database
        $jsondb = $this->db['jsondb'];
        $jsondb = Sanitize::htmlDecode($jsondb);

        // Convert JSON to Array
        $sponsors = json_decode($jsondb, true);

        // Check if the user click on the delete or add
        if (isset($_POST['deleteSponsor'])) {
            $name = $_POST['deleteSponsor'];
            unset($sponsors[$name]);
        } elseif (isset($_POST['addSponsor'])) {
            $name = $_POST['sponsorName'];

            $data = array(
                'prefix'  => $_POST['sponsorPrefix'],
                'name'    => $_POST['sponsorName'],
                'url'     => $_POST['sponsorURL'],
                'suffix'  => $_POST['sponsorSuffix'],
                'logourl' => $_POST['sponsorLogoURL']
            );

            if (empty($name)) {
                return false;
            }

            $sponsors[$name] = $data;
        }

        $this->db['jsondb'] = Sanitize::html(json_encode($sponsors));

        return $this->save();
    }

    // Method called on the settings of the plugin on the admin area
    public function form()
    {
        global $L;

        $html .= '<div class="alert alert-danger" role="alert">';
        $html .= "<p>Hallo 👋 This is " . "<em>" . $this->name() . "</em>" . " plugin speaking. The admin UI feels a bit fragile and limited, but seems to get the job done.</p>";
        $html .= "<p>Please talk with Mace if there is something.</p>";
        $html .= '</div>';

        // New sponsors
        $html .= '<h4 class="mt-3">' . $L->get('Add a new sponsor') . '</h4>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Prefix') . '</label>';
        $html .= '<input name="sponsorPrefix" type="text" dir="auto" class="form-control" value="" placeholder="e.g. The">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Name') . '</label>';
        $html .= '<input name="sponsorName" type="text" dir="auto" class="form-control" value="" placeholder="e.g. RUSTlab">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Url') . '</label>';
        $html .= '<input name="sponsorURL" type="text" dir="auto" class="form-control" value="" placeholder="e.g. https://rustlab.rub.de/">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Suffix') . '</label>';
        $html .= '<input name="sponsorSuffix" type="text" dir="auto" class="form-control" value="" placeholder="e.g. community">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Logo URL') . '</label>';
        $html .= '<input name="sponsorLogoURL" type="text" dir="auto" class="form-control" value="" placeholder="e.g. https://rustlab.ruhr-uni-bochum.de/wp-content/uploads/2023/02/logo-tentacle-500x500-1-1-200x200.png">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<button name="addSponsor" class="btn btn-primary my-2" type="submit">' . $L->get('Add') . '</button>';
        $html .= '</div>';

        // List stored sponsors.
        $jsondb   = $this->getValue('jsondb', $unsanitized = false);
        $sponsors = json_decode($jsondb, true);

        // var_dump($sponsors);

        $html .= !empty($sponsors) ? '<h4 class="mt-3">' . $L->get('Sponsors') . '</h4>' : '';

        foreach ($sponsors as $sponsor) {
            $html .= '<div class="my-2">';

            $html .= '<div>';
            $html .= '<label>' . $L->get('Prefix') . '</label>';
            $html .= '<input type="text" dir="auto" class="form-control" value="' . $sponsor['prefix'] . '" disabled>';
            $html .= '</div>';


            $html .= '<label>' . $L->get('Name') . '</label>';
            $html .= '<input type="text" class="form-control" value="' . $sponsor['name'] . '" disabled">';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<label>' . $L->get('Url') . '</label>';
            $html .= '<input type="text" dir="auto" class="form-control" value="' . $sponsor['url'] . '" disabled>';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<label>' . $L->get('Suffix') . '</label>';
            $html .= '<input type="text" dir="auto" class="form-control" value="' . $sponsor['suffix'] . '" disabled>';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<label>' . $L->get('Logo URL') . '</label>';
            $html .= '<input type="text" dir="auto" class="form-control" value="' . $sponsor['logourl'] . '" disabled>';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<button name="deleteSponsor" class="btn btn-secondary my-2" type="submit" value="' . $sponsor['name'] . '">' . $L->get('Delete') . '</button>';
            $html .= '</div>';
        };

        return $html;
    }

    public function siteBodyEnd()
    {
        $html  = '';
        $html .= "<footer class='sponsors footer'>";
        $html .= "<div class='container'>";
        $html .= "<h5>Sponsored by</h5>";
        $html .= "<div class='sponsor-list'>";
        $html .= "<ul class='list-unstyled'>";

        $jsondb   = $this->getValue('jsondb', false);
        $sponsors = json_decode($jsondb);

        foreach ($sponsors as $sponsor) {
            $html .= empty($sponsor->logourl) ? $this->sponsorAsHtml($sponsor) : $this->sponsorLogoAsHtml($sponsor);
        }

        $html .= "</ul>";
        $html .= "</div>";
        $html .= "</div>";
        $html .= "</footer>";

        return $html;
    }

    public function sponsorAsHtml($sponsor)
    {
        $elem  = '';
        $elem .= '<li class="sponsor">';
        $elem .= $sponsor->prefix . ' ';
        $elem .= '<a href="' . $sponsor->url . '" target="_blank">';
        $elem .= $sponsor->name;
        $elem .= '</a>';
        $elem .= ' ' . $sponsor->suffix;
        $elem .= '</li>';

        return $elem;
    }

    public function sponsorLogoAsHtml($sponsor)
    {
        $fullname = $sponsor->prefix . ' ' . $sponsor->name . ' ' . $sponsor->suffix;

        $elem  = '';
        $elem .= '<li class="sponsor">';
        $elem .= '<a href="' . $sponsor->url . '" target="_blank">';
        $elem .= '<img class="logo" ';
        $elem .= ' src="'   . $sponsor->logourl . '"';
        $elem .= ' title="' . $fullname . '"';
        $elem .= ' alt="'   . $fullname . '"';
        $elem .= '/>';
        $elem .= '</a>';
        $elem .= '</li>';

        return $elem;
    }
}
