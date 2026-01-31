// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product_category.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

ProductCategory _$ProductCategoryFromJson(Map<String, dynamic> json) =>
    ProductCategory(
      id: (json['id'] as num?)?.toInt(),
      name: unescape(json['name']),
      slug: json['slug'] as String?,
      parent: (json['parent'] as num?)?.toInt(),
      categories: ProductCategory.toList(json['categories'] as List?),
      description: json['description'] as String?,
      count: (json['count'] as num?)?.toInt(),
      image: json['image'] as Map<String, dynamic>? ?? {},
    )
      ..display = json['display'] as String?
      ..menuOrder = (json['menu_order'] as num?)?.toInt();

Map<String, dynamic> _$ProductCategoryToJson(ProductCategory instance) =>
    <String, dynamic>{
      'id': instance.id,
      'name': instance.name,
      'slug': instance.slug,
      'parent': instance.parent,
      'categories': instance.categories,
      'description': instance.description,
      'display': instance.display,
      'image': instance.image,
      'menu_order': instance.menuOrder,
      'count': instance.count,
    };
