<?php

namespace Drupal\Tests\drupaleasy_repositories\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test description.
 *
 * @group drupaleasy_repositories
 */
class AddYmlRepoTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stable';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['drupaleasy_repositories'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    // Set up the test here.
  }

  /**
   * Test callback.
   */
  public function testSomething() {
    $admin_user = $this->drupalCreateUser(['access administration pages']);
    $this->drupalLogin($admin_user);
    $this->drupalGet('admin');
    $this->assertSession()->elementExists('xpath', '//h1[text() = "Administration"]');
  }

}
