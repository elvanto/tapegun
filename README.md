# Tapegun

A simple build system written in PHP.

Features:
- Support for tasks written as shell commands or PHP classes
- Multi-target builds with separate environments
- Bundled tasks for Git, archiving and templated text files

## Usage

Tapegun is configured through a JSON file that defines the environment, targets
and tasks required to build a project. The basic structure is as follows.

```json
{
  "name": "project-name",
  "env": {
      "foo": "bar",
      "staging": "/var/app/staging"
  },
  "targets": [
    {
      "name": "dev",
      "env": {
        "foo": "baz"
      }
    },
    {
      "name": "prod",
      "env": {
        "foo": "qux"
      }
    }
  ],
  "pre": [
    {
      "class": "Tapegun.Task.GitClone",
      "env": {
        "git:source": "https://github.com/company/project-name",
        "git:target": "{{staging}}"
      }
    }
  ],
  "build": [
    {
      "description": "Deploying project",
      "command": "project-deploy --foo={{foo}}"
    }
  ],
  "post": [
    {
      "description": "Cleaning staging directory",
      "command": "rm -rf /var/app/staging"
    }
  ]
}
```

### Configuration

__name__

The project name.

__env__

An object mapping environment variable names to values, which can be any data
type support by JSON.

__targets__

An array of objects defining build targets. Each task defined under __build__
will be run once per target and inherit __env__ from both the root and target
configurations.

__pre__

An array of tasks to be run once at the beginning of the build. See __build__
for more details.

__post__

An array of tasks to be run once at the end of the build. See __build__ for
more details.

__build__

An array of tasks to be run once per build target.

The __command__ property is used to execute a shell command. Environment
variables surrounded by `{{` and `}}` will be replaced with their corresponding
values. A custom description can be provided through the __description__
property.

The __class__ property is used to execute a task written in PHP. The namespace
must be provided, with `.` used as a delimiter.

### Environments

Environment variables may be specified on the task, target or root level and
will be resolved in this order.