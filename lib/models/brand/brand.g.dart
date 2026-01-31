// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'brand.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

Brand _$BrandFromJson(Map<String, dynamic> json) => Brand(
      id: (json['id'] as num?)?.toInt(),
      name: json['name'] as String?,
      count: (json['count'] as num?)?.toInt(),
      image: Brand._imageFromJson(json['image']),
      description: json['description'] as String?,
      parent: (json['parent'] as num?)?.toInt(),
      slug: json['slug'] as String?,
    );

Map<String, dynamic> _$BrandToJson(Brand instance) => <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'slug': instance.slug,
      'parent': instance.parent,
      'description': instance.description,
      'count': instance.count,
      'image': instance.image,
    };
