<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'remember_token' => str_random(10),
    ];
});

/** @var \Illuminate\Database\Eloquent\Factory $factory */


$factory->define(App\Event::class, function (Faker\Generator $faker) {

    /**
     * get start and end dates
     */
    
    // instantiate new DateTime instance
    $now = new DateTime();

    // range of days relative to current date
    $rangeOfDays = rand(-730, 730);

    // number of days we will add relative to $now
    $addDays = DateInterval::createFromDateString($rangeOfDays.' days');

    // add days and get unix timestamp
    $unixTimeStamp = '@'.$now->add($addDays)->getTimeStamp();

    // add days and get unix timestamp
    $startDate = new DateTime($unixTimeStamp);

    // hours to append to start time
    $addHours = rand(1, 6);
    
    // end date time
    $endDate = date_modify($now, '+'.$addHours.' hours');

    return [
        'spaceID' => $faker->randomDigitNotNull(),
        'userID' => function () {return factory(App\User::class)->create()->id; },
        'start' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = 'now', $timezone = date_default_timezone_get()),
        'end' => $faker->dateTimeBetween($startDate = 'now', $endDate = $startDate + 100, $timezone = date_default_timezone_get()),
        'status' => $faker->randomElement($array = array ('pending','approved')), // 'b'
        'title' => $faker->word(),
        'description' => $faker->sentence($nbWords = 20, $variableNbWords = true),
        'type' => $faker->randomElement($array = array ('class','meetup','hackathon', 'fundraiser')), // 'b'
        'tags' => implode($faker->randomElements($array = array('html','css','linux', 'taxes', 'finance', 'python', 'marketing', 'writing', 'fitness', 'education,budgeting', 'health,engineering', 'robotics','cloud'))), // 'b'
        'local' => $faker->boolean($chanceOfGettingTrue = 75),
    ];
});
