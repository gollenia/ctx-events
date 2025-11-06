<?php

final class WpLinkProvider
{
    public function event(EventId $id): ResourceLinks
    {
        $friendly  = get_permalink($id->toInt()) ?: null;
        $canonical = home_url('/?p=' . $id->toInt());
        return new ResourceLinks($friendly, $canonical);
    }

    public function eventIri(EventId $id): string
    {
        // stabile API-Ressource als Identifikator
        return home_url('/wp-json/events/v3/event/' . $id->toInt());
    }
}
