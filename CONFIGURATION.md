# Configuration Help

## Bot installation

For general help with the installation, see [README.md](README.md)

## Plugin installation

Plugins are distributed as PHAR-archives. To install them, drop the archives into the plugins directory, restart the bot or run the `reload` command.

## Bot configuration

You can find all settings Fish offers in the `fish.json` file generated on the first startup or in `src/IRC/Resources`.
By default, Fish uses the configuration file found in its root-directory.
This table explains each option in detail:

| Option | Value | Explanation |
|--------|-------|-------------|
| default_nickname | String | The nickname Fish uses to connect |
| default_realname | String | The realname Fish uses to connect |
| default_quitmsg | String | The default quit message to send when Fish quits |
| command_prefix | Array | The characters Fish uses as command-symbols |
| cpu_idle | Int | How long Fish should Idle after a processed message - used for stopping Fish from using all your system capacity |
| authentication_message | Array | Enable/Disable the specified notice sent to users when the bot authenticates them |
| default_ctcp_replies | Array | Specify custom CTCP replies |
| spam_protection | Array | Configuration for the command spam-protection |
| invalid_permissions | String | The message sent to a user when they try to execute a command for which they don't have the permission |
| disable_management | Bool | Allows you to disable the built-in management features |
| auto_reconnect_after_timeout | I nt/False| How long to wait and if Fish should reconnect after a timeout occured |
| max_reconnect_attempts | Int | How often Fish should attempt to reconnect |

There's also an unspecified field for defining your `connection` information. 

| Option | Value | Explanation |
|--------|-------|-------------|
| nickserv | String | Your NickServ password - Optional setting |
| channels | Array | Array of channels to join on connection |

Example: 

```json
"connections": {
    "irc.example.net": {
            "nickserv": "Your NickServ password, if you have one",
            "channels": []
        }
    }
}
```

## User authentication and configuration

To add users Fish should authenticate, create a folder called the name of the network you want to authenticate the users on in the `users` directory.
Create a json-File named `<nickname>.json` that contains the following:

| Option | Value | Explanation |
|--------|-------|-------------|
| admin | Bool | Make user an admin to give them full permissions |
| permissions | Array | An array of permissions a user has |

Example: 

```json
{
    "admin": true,
    "permissions": []
}
```

and Fish will do everything else for you.