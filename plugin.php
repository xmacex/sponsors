<?php

// Copying the functionaliry of the `Links` plugin.

class pluginSponsors extends Plugin
{
    public function init()
    {
        $jsondb = json_encode(array(
            'RUSTlab' => 'https://rustlab.rub.de'
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
            $url  = $_POST['sponsorURL'];
            if (empty($name)) {
                return false;
            }

            $sponsors[$name] = $url;
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
        $html .= '<label>' . $L->get('Name') . '</label>';
        $html .= '<input name="sponsorName" type="text" dir="auto" class="form-control" value="" placeholder="e.g. RUSTlab">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<label>' . $L->get('Url') . '</label>';
        $html .= '<input name="sponsorURL" type="text" dir="auto" class="form-control" value="" placeholder="e.g. https://rustlab.rub.de/">';
        $html .= '</div>';

        $html .= '<div>';
        $html .= '<button name="addSponsor" class="btn btn-primary my-2" type="submit">' . $L->get('Add') . '</button>';
        $html .= '</div>';

        // List stored sponsors.
        $jsondb   = $this->getValue('jsondb', $unsanitized = false);
        $sponsors = json_decode($jsondb, true);

        $html .= !empty($sponsors) ? '<h4 class="mt-3">' . $L->get('Sponsors') . '</h4>' : '';

        foreach ($sponsors as $name => $url) {
            $html .= '<div class="my-2">';
            $html .= '<label>' . $L->get('Name') . '</label>';
            $html .= '<input type="text" class="form-control" value="' . $name . '" disabled">';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<label>' . $L->get('Url') . '</label>';
            $html .= '<input type="text" dir="auto" class="form-control" value="' . $url . '" disabled>';
            $html .= '</div>';

            $html .= '<div>';
            $html .= '<button name="deleteSponsor" class="btn btn-secondary my-2" type="submit" value="' . $name . '">' . $L->get('Delete') . '</button>';
            $html .= '</div>';
        };

        return $html;
    }

    public function siteBodyEnd()
    {
        $html  = '';
        $html .= "<footer class='sponsors footer bg-white'>";
        $html .= "<div class='container'>";
        $html .= "<p>Sponsored by</p>";
        $html .= "<div class='sponsor-list container align-items-center'>";
        $html .= "<ul>";

        $jsondb   = $this->getValue('jsondb', false);
        $sponsors = json_decode($jsondb);

        foreach ($sponsors as $name => $url) {
            $html .= '<li class="sponsor">';
            $html .= '<a href="' . $url . ' target=_blank">';
            $html .= $name;
            $html .= '</a>';
            $html .= '</li>';
        }

        $html .= "</ul>";
        $html .= "</div>";
        $html .= "</footer>";

        return $html;
    }
}
