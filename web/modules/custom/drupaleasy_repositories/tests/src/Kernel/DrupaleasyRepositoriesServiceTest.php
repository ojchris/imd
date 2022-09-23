<?php

namespace Drupal\Tests\drupaleasy_repositories\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService;
use Drupal\Tests\drupaleasy_repositories\Traits\RepositoryContentTypeTrait;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Test description.
 *
 * @group drupaleasy_repositories
 */
class DrupaleasyRepositoriesServiceTest extends KernelTestBase {

  use RepositoryContentTypeTrait;

  /**
   * The drupaleasy_repositories service.
   *
   * @var \Drupal\drupaleasy_repositories\DrupaleasyRepositoriesService
   */
  protected DrupaleasyRepositoriesService $drupaleasyRepositoriesService;

  /**
   * The admin user property.
   *
   * @var \Drupal\user\UserInterface
   */
  protected UserInterface $adminUser;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected ModuleHandler $moduleHandler;


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'drupaleasy_repositories',
    'node',
    'field',
    'user',
    'system',
    // For text_long field types.
    'text',
    // For link field types.
    'link',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupaleasyRepositoriesService = $this->container->get('drupaleasy_repositories.service');
    $this->createRepositoryContentType();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');

    $aquaman_repo = $this->getAquamanRepo();
    $repo = reset($aquaman_repo);

    $this->adminUser = User::create(['name' => $this->randomString()]);
    $this->adminUser->save();
    $this->container->get('current_user')->setAccount($this->adminUser);

    $node = Node::create([
      'type' => 'repository',
      'title' => $repo['label'],
      'field_machine_name' => array_key_first($aquaman_repo),
      'field_url' => $repo['url'],
      'field_hash' => '06ec2efe7005ae32f624a9c2d28febd5',
      'field_number_of_issues' => $repo['num_open_issues'],
      'field_source' => $repo['source'],
      'field_description' => $repo['description'],
      'user_id' => $this->adminUser->id(),
    ]);
    $node->save();

  }

  /**
   * Data provider for testIsUnique().
   *
   * @return array
   *   Test data and expected results.
   */
  public function provideTestIsUnique(): array {
    $unique_repo_info['superman-repo'] = [
      'label' => 'The Superman repository',
      'description' => 'This is where Superman keeps all his crime-fighting code.',
      'num_open_issues' => 0,
      'source' => 'yml',
      'url' => 'https://imd-summer-2022.ddev.site/superman-repo.yml',
    ];
    return [
      [FALSE, $this->getAquamanRepo()],
      [TRUE, $unique_repo_info],
    ];
  }

  /**
   * Test the ability for the service to ensure repositories are unique.
   *
   * @covers ::isUnique
   * @dataProvider provideTestIsUnique
   * @test
   */
  public function testIsUnique(bool $expected, array $repo): void {
    // Use reflection to make isUnique() public.
    $reflection_is_unique = new \ReflectionMethod($this->drupaleasyRepositoriesService, 'isUnique');
    $reflection_is_unique->setAccessible(TRUE);
    $actual = $reflection_is_unique->invokeArgs(
      $this->drupaleasyRepositoriesService,
      // Use $uid = 999 to ensure it is different from $this->adminUser.
      [$repo, 999]
    );
    $this->assertEquals($expected, $actual);
  }

  /**
   * Data provider for testValidateRepositoryUrls().
   *
   * @return array
   *   Test data and expected results.
   */
  public function provideValidateRepositoryUrls(): array {
    // This is run before setup() and other things so $this->container
    // isn't available here!
    return [
      ['', [['uri' => '/tests/assets/batman-repo.yml']]],
      ['is not valid', [['uri' => '/tests/assets/batman-repo.ym']]],
    ];
  }

  /**
   * Test the ability for the service to ensure repositories are valid.
   *
   * @covers ::validateRepositoryUrls
   * @dataProvider provideValidateRepositoryUrls
   * @test
   */
  public function testValidateRepositoryUrls(string $expected, array $urls): void {

    // Get the full path to the test .yml file.
    /** @var \Drupal\Core\Extension\Extension $module */
    $module = $this->moduleHandler->getModule('drupaleasy_repositories');
    $module_full_path = \Drupal::request()->getUri() . $module->getPath();

    foreach ($urls as $key => $url) {
      if (isset($url['uri'])) {
        $urls[$key]['uri'] = $module_full_path . $url['uri'];
      }
    }

    $actual = $this->drupaleasyRepositoriesService->validateRepositoryUrls($urls, 999);
    /* if ($expected) {
    $this->assertTrue((bool) mb_stristr($actual, $expected), "The URLs'
    validation does not match the expected value. Actual: {$actual},
    Expected: {$expected}");
    }
    else {
    $this->assertEquals($expected, $actual, "The URLs' validation does not match
    the expected value. Actual: {$actual}, Expected: {$expected}");
    } */

    // Only check assertion if no error is expected nor returned.
    if (($expected != '') || ($actual != $expected)) {
      // $repo = reset($repo);
      $this->assertTrue((bool) mb_stristr($actual, $expected), "The URLs' validation error ' . $actual . ' does not match the expected value.");
    }
  }

}
