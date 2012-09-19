# itunes-podcast-feed

A php script for generating iTunes-compatible podcast feeds.

Can optionally use the [getID3 library](http://getid3.sourceforge.net/) for
reading information about each file from the audio ID3 tags.


## Usage

 1. Place feed.php anywhere on your server. Optionally download the latest
    version of getID3 and place it somewhere nearby.
 2. Edit the configuration variables in feed.php to match your server set up
    all audio files must be within the same directory.
 3. The URL to feed.php _is_ your feed url.
    [Submit this to iTunes](http://support.apple.com/kb/HT1819) & enjoy.

## getID3

Without getID3 enabled, the script will output file data of the form

```xml
<item>
    <title>MP3 File</title>
    <link>
        http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3
    </link>
    <itunes:author>Nexus Church Australia</itunes:author>
    <itunes:category text="Religion & Spirituality">
        <itunes:category text="Christianity"/>
    </itunes:category>
    <category>Music</category>
    <duration/>
    <description/>
    <pubDate>August 24 2012</pubDate>
    <enclosure url="http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3" length="27648828" type="audio/mpeg"/>
    <guid>
        http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3
    </guid>
    <author>info@nexuschurch.com</author>
</item>
```

By including getID3, the files will also have information about the specific
file title, artist and duration.

```xml
<item>
    <title>Birth: Wind And Fire</title>
    <link>
        http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3
    </link>
    <itunes:author>Jamie Haith</itunes:author>
    <itunes:category text="Religion & Spirituality">
        <itunes:category text="Christianity"/>
    </itunes:category>
    <category>Music</category>
    <duration>38:24</duration>
    <description/>
    <pubDate>August 24 2012</pubDate>
    <enclosure url="http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3" length="27648828" type="audio/mpeg"/>
    <guid>
        http://nexuschurch.com.au/sites/all/files/sermon_audio/03062012_AM.mp3
    </guid>
    <author>info@nexuschurch.com</author>
</item>
```





