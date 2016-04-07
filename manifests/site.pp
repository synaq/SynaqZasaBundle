exec {"apt-get update":
  path => "/usr/bin",
}

package { ["php5-common", "php5-cli", "php5-intl", "php5-mcrypt", "php5-curl", "mcrypt", "git", "php5-xdebug"]:
  ensure => installed,
  require => [Exec["apt-get update"]],
}

exec { "/usr/sbin/php5enmod mcrypt" :
  require => Package['php5-mcrypt']
}

package { "curl":
  ensure => installed,
}

exec { 'install composer':
  command => '/usr/bin/curl -sS https://getcomposer.org/installer | HOME=/home/vagrant php && sudo mv composer.phar /usr/local/bin/composer',
  require => [Package['php5-cli'], Package['curl']],
}