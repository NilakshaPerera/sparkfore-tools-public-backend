@foreach ($productChanges["softwareAdditions"] as $softwareAddition)
+ {{$softwareAddition["name"]}} {{$softwareAddition["version_type"]}}:{{$softwareAddition["version"]}}
@endforeach
@foreach ($productChanges["softwareChanges"] as $softwareChange)
~ {{$softwareChange["name"]}} {{$softwareChange["from_version_type"]}}:{{$softwareChange["from"]}} -> {{$softwareChange["to_version_type"]}}:{{$softwareChange["to"]}}
@endforeach
@foreach ($productChanges["addedPlugins"] as $pluginAddition)
+ {{$pluginAddition["name"]}} {{$pluginAddition["version_type"]}}:{{$pluginAddition["selected_version"]}}
@endforeach
@foreach ($productChanges["pluginChanges"] as $pluginChange)
~ {{$pluginChange["name"]}} {{$pluginChange["from_version_type"]}}:{{$pluginChange["from"]}} -> {{$pluginChange["to_version_type"]}}:{{$pluginChange["to"]}}
@endforeach
@foreach ($productChanges["removedPlugins"] as $pluginDeletion)
- {{$pluginDeletion["name"]}}  {{$pluginDeletion["version_type"]}}:{{$pluginDeletion["selected_version"]}}
@endforeach
