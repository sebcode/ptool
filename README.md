# ptool

Commandline scripts to quickly navigate through source code files.

In my developement setup I usually work with vim on the commandline. I use these scripts to quickly find files based on a search pattern and open them in vim.

Typical workflow:

 * I want to edit a file from which I know that its filename contains `basemodel`. So I enter

        e basemodel

 * Since the project has multiple files matching this pattern, I get a list of all matches and can choose the right one:

        > (100)   0  server/app/Models/BaseModel.php
          (100)   1  testserver/app/BaseModel.php
        
        Select?

 * The selection can be changed with `j` and `k` (down/up) and confirmed with return. Then the selected file opens in vim.
      
 * If I just want to go to the directory that contains this file (to create a new file for example), I use the command `g` instead of `e`:

        g basemodel

 * If there is only one match for the pattern, the file is directly opened in the editor.

 * The files are sorted based on rules which can be defined in `e.conf.php` in the project directory.

 * Colors highlighting for search results: files that are already open are highlighted in purple, files that have git modifications are highlighted in green.

### Requirements

 * PHP 5.6+ (`php` must be accessible via `/usr/bin/env`)

### Setup

 * `cd && git clone https://github.com/sebcode/ptool.git`
 * Add this to `.bashrc`:

        source ~/ptool/shell
        DEVPATH="$(~/ptool/getpath.php)"
        DEVSHELL="$DEVPATH/shell"
        test -f "$DEVSHELL" && {
          source "$DEVSHELL"
        }

 * Create symlinks (`~/bin` must be in `PATH`)

        ln -sf "~/ptool/e.php" ~/bin/e
        ln -sf "~/ptool/ptool.php" ~/bin/ptool

### Directory structure

In my setup, all projects have their own directory under `~/dev/`. A project directory usually has the following common contents:

 * `.alias` file with contains the project alias. For example `~/dev/TestProject/.alias` may contain `tp`. With `pt tp` I can switch to that project.
 * `shell`: If this file exists, it will be sourced for every new shell or when you switch to that project. May contain project specific shell aliases for example.
 * `todo.txt`: Todo file, the command `N` opens this file in the editor.
 * `e.conf.php`: Ruleset for the commands `e` and `g`
 * `.repo` contains a list of the subdirectories that contain the git repositories of the project (newline separated).
 * One or multiple git repositories.

### Ruleset file format for `e.conf.php`

The ruleset returns a PHP hash array with pattern/priority pairs. Search results are sorted based on the priorities. Example:

    <?php return [
      '\.php$' => 100,
      '\.js' => 90,
      '\.mustache' => 80,
      '^server/vendor/' => -500,
      '^server/app/storage/' => -500,
      '^attic/' => -500,
      '^tmp/' => -500,
    ];

### Usage

 * `pt [project]`
    switch project or show current
 * `e` go to current project directory
 * `e [pattern]` search and prompt for file in project
 * `e -a` show all files by score and prompt for file
 * `e -g` show all "git status" files by score and prompt
 * `g [pattern]` same as e, but never open file but cd to the directory
 * `N` open notes file for the current project

### Credits

Sebastian Volland - http://github.com/sebcode

Licensed under the terms of the MIT license (see LICENSE file).