{{-- Renders a Bangla + English pair of fields: name_bn / name_en --}}
@props(['name', 'label', 'model' => null, 'textarea' => false, 'rows' => 4, 'help' => null, 'required' => false])
<div class="row">
    @foreach(['bn' => 'বাংলা', 'en' => 'English'] as $lang => $langLabel)
        <div class="col-md-6">
            @if($textarea)
                <x-form.textarea :name="$name.'_'.$lang" :label="$label.' ('.$langLabel.')'" :value="$model?->{$name.'_'.$lang}"
                                 :rows="$rows" :help="$help" :required="$required" :lang="$lang" />
            @else
                <x-form.input :name="$name.'_'.$lang" :label="$label.' ('.$langLabel.')'" :value="$model?->{$name.'_'.$lang}"
                              :help="$help" :required="$required" :lang="$lang" />
            @endif
        </div>
    @endforeach
</div>
