
cli utils to quickly navigate through source code

--

pt [project]
    switch project or show current

e
    go to current project dir
e [pattern]
    search and prompt for file in project
e -a
    show all files by score and prompt for file
e -g
    show all "git status" files by score and prompt

g [pattern]
    same as e, but never open file but cd to the directory

N
    open notes file for the current project

