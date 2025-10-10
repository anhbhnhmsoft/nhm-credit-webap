<?php

namespace App\Filament\Resources\PageStatics\Schemas;

use App\Models\PageStatic;
use App\Utils\Constants\CommonStatus;
use App\Utils\Constants\PageStaticType;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;


class PageStaticForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                            ->label('Tiêu đề')
                            ->trim()
                            ->disabled(function ($get){
                                if (!empty($get('id')) && $get('type') == PageStaticType::FIXED->value){
                                    return true;
                                }
                                return false;
                            })
                            ->minLength(10)
                            ->maxLength(255)
                            ->placeholder('Tối thiểu 10 kí tự, tối đa 255 kí tự')
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (!$state) {
                                    $set('slug', '');
                                    return;
                                };
                                $set('slug', Str::slug($state));
                            })
                            ->required()
                            ->validationMessages([
                                'required' => 'Vui lòng nhập tiêu đề.',
                                'minLength' => 'Tên tiêu đề phải có ít nhất 10 ký tự.',
                                'maxLength' => 'Tên tiêu đề không được vượt quá 255 ký tự.',
                            ]),
                            Textarea::make('icon_svg')
                            ->label('Icon SVG')
                            ->rows(3)
                            ->columnSpanFull()
                            ->helperText(new HtmlString(
                                'Icon SVG lấy từ Heroicons. <a href="https://heroicons.com" target="_blank" rel="noopener" class="underline underline-offset-2 decoration-2 text-primary-600 hover:decoration-4" style="text-decoration: underline;">Bấm vào đây để lấy icon</a>.'
                            )),
                RichEditor::make('content')
                    ->label('Nội dung')
                    ->required()
                    ->columnSpanFull()
                    ->extraAttributes(['style' => 'min-height: 300px;']),
                    TextInput::make('slug')
                    ->label('Đường dẫn slug')
                    ->disabled(function ($get){
                        if (!empty($get('id')) && $get('type') == PageStaticType::FIXED->value){
                            return true;
                        }
                        return false;
                    })
                    ->minLength(10)
                    ->maxLength(255)
                    ->trim()
                    ->required()
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->helperText('Slug sẽ tự sinh ra khi bạn nhập tiêu đề, hoặc bạn có thể tự sửa theo mong muốn')
                    ->rules([
                        fn(Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                            $id = $get('id');
                            if ($id) {
                                $check = PageStatic::query()
                                    ->where('slug', $value)
                                    ->where('id', '!=', $id)
                                    ->exists();
                            } else {
                                $check = PageStatic::query()->where('slug', $value)->exists();
                            }
                            if ($check) {
                                $fail('Slug này đã có bài viết được sử dụng');
                            }
                        },
                    ])
                    ->validationMessages([
                        'required' => 'Vui lòng nhập slug.',
                        'regex' => 'Slug chỉ được chứa chữ thường, số và dấu gạch ngang, không có ký tự đặc biệt hoặc khoảng trắng.',
                        'minLength' => 'Phải có ít nhất 10 ký tự.',
                        'maxLength' => 'Không được vượt quá 255 ký tự.',
                    ]),
                Select::make('status')
                    ->required()
                    ->options(CommonStatus::getOptions())
                    ->default(CommonStatus::ACTIVE->value),
            ]);
    }
}
