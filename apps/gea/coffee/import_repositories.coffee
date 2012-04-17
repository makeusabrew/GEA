async    = require 'async'
zmq      = require 'zmq'
mysql    = require 'mysql'
exec     = require('child_process').exec
spawn    = require('child_process').spawn

Settings = require '../../../jslib/settings'

client = mysql.createClient({
    user:     Settings.getValue "db", "user"
    password: Settings.getValue "db", "pass"
    database: Settings.getValue "db", "dbname"
})


queue = zmq.socket 'pull'
queue.bind 'tcp://127.0.0.1:8989', (err) ->
    console.log 'bound pull queue'
    throw err if err?

queue.on 'message', (data) ->
    ids = JSON.parse data
    qStr = ''
    clonedIds = []

    for id,i in ids
        qStr += '?'
        if i < ids.length-1
            qStr += ','

    client.query "SELECT * FROM `repositories` WHERE ID IN(#{qStr})", ids, (err, results) ->
        throw err if err?
        async.forEachLimit results, 10, (item, fn) ->
            cloneCmd = "git clone --bare #{item.clone_url} /tmp/repo_#{item.id}"
            console.log cloneCmd
            exec cloneCmd, (err, stdout, stderr) ->
                throw err if err?
                clonedIds.push({
                    id: item.id,
                    github_id: item.github_id
                })
                #client.query "UPDATE `repositories` set `status` = ? WHERE `id` = ?", "parsing", item.id, (err, results) ->
                fn()
        , (err) ->
            throw err if err?
            console.log "Done cloning, now processing logs"
            async.forEachLimit clonedIds, 10, (item, fn) ->
                log = spawn 'git', ['log', '--pretty=format:"%H|%aE|%at|%s"'], {cwd: "/tmp/repo_#{item.id}"}
                
                log.stdout.on 'data', (data) ->
                    lines = data.toString('utf8').split /\n/
                    for line in lines
                        matches = line.match /^\"(.+?)\|(.+?)\|(.+?)\|(.+)\"$/
                        if matches
                            matches.shift()
                            fields = [item.id].concat matches
                            if fields.length == 5
                                query = "INSERT INTO `commits` (`repository_id`,`hash`,`email`,`date`,`message`) VALUES (?,?,?,?,?)"
                                client.query query, fields
                            else
                                console.log "wrong field length!"
                                console.log fields.length
                        else
                            console.log "no matches, assume blank line [#{line}]"

                log.on 'exit', (code) ->
                    fn()
            , (err) ->
                throw err if err?
                console.log "All done"
                #queue.close()
                #client.end ->
                #process.exit 0
