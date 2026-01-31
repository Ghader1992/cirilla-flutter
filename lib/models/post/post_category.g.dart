// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'post_category.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

PostCategory _$PostCategoryFromJson(Map<String, dynamic> json) => PostCategory(
      id: (json['id'] as num?)?.toInt(),
      name: unescape(json['name']),
      count: (json['count'] as num?)?.toInt(),
      parent: (json['parent'] as num?)?.toInt(),
      description: json['description'] as String?,
      slug: json['slug'] as String?,
    )..image = PostCategory._imageFromJson(json['acf']);

Map<String, dynamic> _$PostCategoryToJson(PostCategory instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'count': instance.count,
      'parent': instance.parent,
      'description': instance.description,
      'slug': instance.slug,
      'acf': PostCategory._imageToJson(instance.image),
    };
