<?php

namespace Tests\Feature;

use JoelButcher\Socialstream\Providers;
use Tests\TestCase;

class SocialstreamConfigTest extends TestCase
{
    public function test_socialstream_config_has_social_media_providers(): void
    {
        $providers = config('socialstream.providers', []);

        $this->assertContains(Providers::bitbucket(), $providers);
        $this->assertContains(Providers::facebook(), $providers);
        $this->assertContains(Providers::github(), $providers);
        $this->assertContains(Providers::gitlab(), $providers);
        $this->assertContains(Providers::google(), $providers);
        $this->assertContains(Providers::linkedin(), $providers);
        $this->assertContains(Providers::linkedinOpenId(), $providers);
        $this->assertContains(Providers::slack(), $providers);
        $this->assertContains(Providers::twitterOAuth2(), $providers);
        $this->assertNotContains(Providers::twitterOAuth1(), $providers);
    }
}
